#!/bin/bash

set -e
echo "Updating site..."

# Define variables for easy updates
REPO_URL="https://github.com/allweather541/BaseballTrackerAzure.git"
TEMP_DIR="/tmp/webapp"
WEB_DIR="/var/www/html"

# Safely handle the temporary directory
if [ -d "$TEMP_DIR" ]; then
    echo "Cleaning up existing temporary directory..."
    sudo rm -rf "$TEMP_DIR"
fi

# 2. Clone the repository
echo "Cloning the baseball tracker repository..."
sudo git clone "$REPO_URL" "$TEMP_DIR"

# 3. Verify the file exists before attempting to move it
if [ -f "$TEMP_DIR/index.php" ]; then
    echo "Deploying index.php to web directory..."
    sudo cp "$TEMP_DIR/index.php" "$WEB_DIR/"
else
    echo "Deployment failed: index.php not found in the repository."
    exit 1
fi
# 4. Update the environment string
if [ -f "$WEB_DIR/index.php" ]; then
    echo "Updating environment tag for VM 1..."
    sudo sed -i 's/Running on Cloud Server/Running on VM 1/' "$WEB_DIR/index.php"
else
    echo "Warning: Target file for sed substitution not found."
fi

# 5. Clean up
echo "Cleaning up temporary files..."
sudo rm -rf "$TEMP_DIR"

echo "Deployment completed successfully!"
