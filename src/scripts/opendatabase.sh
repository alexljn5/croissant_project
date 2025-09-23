#!/bin/bash

# Cream's note: This script opens a terminal straight into your MySQL database.
# It's great for quick checks or updates!

# Check if the Docker container is running
if ! sudo docker ps | grep -q croissant_project-db-1; then
    echo "Oh dear! The container 'croissant_project-db-1' isn't running. Please start it up!"
    exit 1
fi

# Open the interactive MySQL shell
sudo docker exec -it croissant_project-db-1 mysql -u root -ppassword croissantdb

echo "All done! Hope that was fun. If you need more help, Cheese and I are ready! 🐰🧀"