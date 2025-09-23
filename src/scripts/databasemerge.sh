#!/bin/bash

# Cream's note: This script imports croissantdb.sql into the Docker MySQL container.
# It runs in the background so you can keep working! Make sure your container is up.

# Check if the Docker container is running
if ! sudo docker ps | grep -q croissant_project-db-1; then
    echo "Oh no! The container 'croissant_project-db-1' isn't running. Start it first, okay?"
    exit 1
fi

# Path to the SQL file (relative to src/)
SQL_FILE="../../databases/croissantdb.sql"

# Check if the SQL file exists
if [ ! -f "$SQL_FILE" ]; then
    echo "Oopsie! I can't find the SQL file at $SQL_FILE. Please check the path!"
    exit 1
fi

# Run the import in the background
sudo docker exec -i croissant_project-db-1 mysql -u root -ppassword croissantdb < "$SQL_FILE" &

echo "Yay! The merge started in the background. Check on it with 'sudo docker logs croissant_project-db-1' if you want!"