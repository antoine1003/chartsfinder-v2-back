#!/bin/bash

set -e

# Default to patch if no argument is given
BUMP_TYPE=${1:-patch}

# Validate bump type
if [[ "$BUMP_TYPE" != "patch" && "$BUMP_TYPE" != "minor" && "$BUMP_TYPE" != "major" ]]; then
  echo "Usage: $0 [patch|minor|major]"
  exit 1
fi

# Get current version from composer.json
CURRENT_VERSION=$(composer.phar config version)
IFS='.' read -r -a parts <<< "$CURRENT_VERSION"

MAJOR=${parts[0]}
MINOR=${parts[1]}
PATCH=${parts[2]}

# Bump version
case $BUMP_TYPE in
  patch)
    PATCH=$((PATCH + 1))
    ;;
  minor)
    MINOR=$((MINOR + 1))
    PATCH=0
    ;;
  major)
    MAJOR=$((MAJOR + 1))
    MINOR=0
    PATCH=0
    ;;
esac

NEW_VERSION="${MAJOR}.${MINOR}.${PATCH}"
echo "Bumping version: $CURRENT_VERSION → $NEW_VERSION"

# Update composer.json
composer.phar config version "$NEW_VERSION"

# Commit and tag
git add composer.json
git commit -m "chore: bump version to $NEW_VERSION"
git tag "v$NEW_VERSION"
git push
git push origin "v$NEW_VERSION"

echo "✅ Version bumped and pushed: $NEW_VERSION"
