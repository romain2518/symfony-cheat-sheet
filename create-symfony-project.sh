#!/bin/bash
set -e

PROJECT_NAME=$1
GIT_URL=$2

if [ $# -lt 2 ]; then
    echo "Usage: create-symfony-project <project-name> <git-url>"
    echo "Example: create-symfony-project my-project https://github.com/username/my-project.git"
    exit 1
fi

echo "🚀 Creating Symfony project: $PROJECT_NAME"

# 1. Clone
git clone https://github.com/dunglas/symfony-docker.git "$PROJECT_NAME"
cd "$PROJECT_NAME"

# 2. Clean up
rm -rf .git* docs/ README.md

# 3. Git init
git init
git add .
git commit -m "Initial commit: symfony-docker template"
git remote add origin "$GIT_URL"
git push -u origin main

# 4. Docker build & up
echo "🐳 Building and starting Docker services..."
docker compose build --pull --no-cache
docker compose up --wait

echo "✅ Project created and ready!"
echo "📝 Default credentials:"
echo "  Database: app"
echo "  User: app"
echo "  Password: !ChangeMe!"
