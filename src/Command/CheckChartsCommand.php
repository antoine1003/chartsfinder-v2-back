<?php

namespace App\Command;

use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\Runway;
use App\Repository\AirportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:check:charts',
    description: 'Checks X random chart URLs and sends an email if any fail',
)]
class CheckChartsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
        private readonly MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('count', InputArgument::OPTIONAL, 'Number of random charts to check', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $chartsPerRegion = (int) $input->getArgument('count');

        // Step 1: Get distinct ICAO region prefixes
        $connection = $this->em->getConnection();
        $prefixes = $connection->executeQuery("
            SELECT DISTINCT SUBSTRING(a.icao_code, 1, 2) AS prefix
            FROM airport a
            JOIN chart c ON c.airport_id = a.id
            WHERE a.icao_code IS NOT NULL
        ")->fetchFirstColumn();

        $successes = [];
        $failures = [];

        foreach ($prefixes as $prefix) {
            $rawChartIds = $connection->executeQuery(
                'SELECT c.id FROM chart c
         INNER JOIN airport a ON c.airport_id = a.id
         WHERE a.icao_code LIKE :prefix
         ORDER BY RAND()
         LIMIT :limit',
                [
                    'prefix' => $prefix . '%',
                    'limit' => $chartsPerRegion,
                ],
                [
                    'prefix' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchFirstColumn();

            $charts = $this->em->getRepository(Chart::class)->findBy(['id' => $rawChartIds]);

            foreach ($charts as $chart) {
                try {
                    $response = $this->httpClient->request('GET', $chart->getUrl(), [
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0 Safari/537.36',
                        ],
                        'max_redirects' => 5,
                        'timeout' => 30,
                    ]);

                    $statusCode = $response->getStatusCode();

                    if ($statusCode === 200) {
                        $successes[] = $chart;
                    } else {
                        $failures[] = [
                            'chart' => $chart,
                            'status' => $statusCode,
                        ];
                    }
                } catch (\Throwable $e) {
                    $failures[] = [
                        'chart' => $chart,
                        'status' => $e->getMessage(),
                    ];
                }
            }
        }

        $this->sendSummaryEmail($successes, $failures);

        if (count($failures) > 0) {
            $io->error(sprintf('%d chart(s) failed to load', count($failures)));
        } else {
            $io->success('All selected charts are reachable.');
        }

        return Command::SUCCESS;
    }

    private function sendSummaryEmail(array $successes, array $failures): void
    {
        $totalCharts = count($successes) + count($failures);
        $successRate = $totalCharts > 0 ? round((count($successes) / $totalCharts) * 100, 1) : 0;
        $timestamp = (new \DateTime())->format('F j, Y \a\t g:i A');

        $html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart URL Check Summary</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px 30px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Chart URL Check Summary</h1>
                            <p style="margin: 8px 0 0; color: #e0e7ff; font-size: 14px;">' . $timestamp . '</p>
                        </td>
                    </tr>

                    <!-- Stats Overview -->
                    <tr>
                        <td style="padding: 30px;">
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 33.33%; text-align: center; padding: 15px; background-color: #f8fafc; border-radius: 6px;">
                                        <div style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">' . $totalCharts . '</div>
                                        <div style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Checked</div>
                                    </td>
                                    <td style="width: 10px;"></td>
                                    <td style="width: 33.33%; text-align: center; padding: 15px; background-color: #f0fdf4; border-radius: 6px;">
                                        <div style="font-size: 28px; font-weight: 700; color: #16a34a; margin-bottom: 4px;">' . count($successes) . '</div>
                                        <div style="font-size: 12px; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px;">Successful</div>
                                    </td>
                                    <td style="width: 10px;"></td>
                                    <td style="width: 33.33%; text-align: center; padding: 15px; background-color: #fef2f2; border-radius: 6px;">
                                        <div style="font-size: 28px; font-weight: 700; color: #dc2626; margin-bottom: 4px;">' . count($failures) . '</div>
                                        <div style="font-size: 12px; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px;">Failed</div>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 20px; padding: 12px; background-color: #f8fafc; border-left: 4px solid ' . ($successRate >= 95 ? '#16a34a' : ($successRate >= 80 ? '#f59e0b' : '#dc2626')) . '; border-radius: 4px;">
                                <span style="font-size: 14px; color: #64748b;">Success Rate: </span>
                                <span style="font-size: 16px; font-weight: 600; color: #1e293b;">' . $successRate . '%</span>
                            </div>
                        </td>
                    </tr>';

        // Failed Charts Section
        if (count($failures) > 0) {
            $html .= '
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <h2 style="margin: 0 0 15px; color: #dc2626; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                                <span style="display: inline-block; width: 24px; height: 24px; margin-right: 8px; font-size: 18px;">❌</span>
                                Failed Charts (' . count($failures) . ')
                            </h2>
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">';

            foreach ($failures as $fail) {
                /** @var Chart $chart */
                $chart = $fail['chart'];
                $html .= '
                                <tr>
                                    <td style="padding: 12px; background-color: #fef2f2; border-left: 3px solid #dc2626; margin-bottom: 8px; border-radius: 4px;">
                                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">' . htmlspecialchars($chart->getName()) . '</div>
                                        <div style="font-size: 13px; color: #64748b; margin-bottom: 6px;">
                                            ICAO: <span style="font-weight: 500; color: #475569;">' . htmlspecialchars($chart->getAirport()?->getIcaoCode() ?? 'N/A') . '</span>
                                            <span style="margin: 0 8px; color: #cbd5e1;">•</span>
                                            <span style="color: #dc2626; font-weight: 500; font-family: monospace;">' . htmlspecialchars($fail['status']) . '</span>
                                        </div>
                                        <div style="font-size: 12px;">
                                            <a href="' . htmlspecialchars($chart->getUrl()) . '" style="color: #667eea; text-decoration: none; word-break: break-all;">' . htmlspecialchars($chart->getUrl()) . '</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr><td style="height: 8px;"></td></tr>';
            }

            $html .= '
                            </table>
                        </td>
                    </tr>';
        }


        // Successful Charts Section
        if (count($successes) > 0) {
            $html .= '
                    <tr>
                        <td style="padding: 0 30px 20px;">
                            <h2 style="margin: 0 0 15px; color: #16a34a; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                                <span style="display: inline-block; width: 24px; height: 24px; margin-right: 8px; font-size: 18px;">✅</span>
                                Successful Charts (' . count($successes) . ')
                            </h2>
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">';

            foreach ($successes as $chart) {
                $html .= '
                                <tr>
                                    <td style="padding: 12px; background-color: #f9fafb; border-left: 3px solid #16a34a; margin-bottom: 8px; border-radius: 4px;">
                                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">' . htmlspecialchars($chart->getName()) . '</div>
                                        <div style="font-size: 13px; color: #64748b; margin-bottom: 6px;">
                                            ICAO: <span style="font-weight: 500; color: #475569;">' . htmlspecialchars($chart->getAirport()?->getIcaoCode() ?? 'N/A') . '</span>
                                            <span style="margin: 0 8px; color: #cbd5e1;">•</span>
                                            <span style="color: #16a34a; font-weight: 500;">200 OK</span>
                                        </div>
                                        <div style="font-size: 12px;">
                                            <a href="' . htmlspecialchars($chart->getUrl()) . '" style="color: #667eea; text-decoration: none; word-break: break-all;">' . htmlspecialchars($chart->getUrl()) . '</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr><td style="height: 8px;"></td></tr>';
            }

            $html .= '
                            </table>
                        </td>
                    </tr>';
        }


        // Footer
        $html .= '
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8fafc; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                Automated chart monitoring by ChartsFinder
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

        $email = (new Email())
            ->from('noreply@chartsfinder.com')
            ->to('antoine.dautry@gmail.com')
            ->subject(sprintf(
                'Chart Check: %d/%d Successful (%s%%)',
                count($successes),
                $totalCharts,
                $successRate
            ))
            ->html($html);

        $this->mailer->send($email);
    }
}
