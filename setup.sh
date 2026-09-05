#!/bin/bash
# ============================================================
# setup.sh — One-shot setup for MediStock MVC on XAMPP
# Run with: sudo bash setup.sh
# ============================================================

set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
XAMPP_DIR="/opt/lampp"
HTDOCS_DIR="$XAMPP_DIR/htdocs"
DEPLOY_DIR="$HTDOCS_DIR/medistock"
DB_NAME="medistock_mvc"
DB_USER="root"
DB_PASS=""

echo "========================================="
echo "  MediStock MVC — Setup Script"
echo "========================================="

# 1. Start XAMPP services
echo ""
echo "[1/4] Starting XAMPP services..."
$XAMPP_DIR/lampp start
echo "      ✓ XAMPP started"

# 2. Create database and import schema
echo ""
echo "[2/4] Importing database schema..."
$XAMPP_DIR/bin/mysql -u "$DB_USER" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$XAMPP_DIR/bin/mysql -u "$DB_USER" "$DB_NAME" < "$PROJECT_DIR/database/schema.sql"
echo "      ✓ Database '$DB_NAME' imported"

# 3. Copy project to htdocs
echo ""
echo "[3/4] Deploying to $DEPLOY_DIR..."
if [ -d "$DEPLOY_DIR" ]; then
    rm -rf "$DEPLOY_DIR"
fi
cp -r "$PROJECT_DIR" "$DEPLOY_DIR"

# Remove setup script from deployed copy
rm -f "$DEPLOY_DIR/setup.sh"

# Ensure uploads directory is writable
mkdir -p "$DEPLOY_DIR/uploads/avatars"
chmod -R 777 "$DEPLOY_DIR/uploads"
echo "      ✓ Project deployed"

# 4. Print success message
echo ""
echo "[4/4] Setup complete!"
echo ""
echo "========================================="
echo "  ✓ Open: http://localhost/medistock/public/"
echo "  ✓ Login: admin / admin123"
echo "  ✓ Database: $DB_NAME"
echo "========================================="
echo ""
