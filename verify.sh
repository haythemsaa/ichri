#!/bin/bash

#===============================================================================
# ichri.tn - Verification Script
# Tests that all components are working correctly
#===============================================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

# Functions
print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}   ichri.tn - Verification Script${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

test_pass() {
    echo -e "${GREEN}✓${NC} $1"
    ((PASSED++))
}

test_fail() {
    echo -e "${RED}✗${NC} $1"
    ((FAILED++))
}

test_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
    ((WARNINGS++))
}

print_section() {
    echo -e "\n${BLUE}▶${NC} $1"
}

# Start verification
print_header

#===============================================================================
# 1. Check Docker Services
#===============================================================================
print_section "Checking Docker Services..."

if docker-compose ps | grep -q "backend.*Up"; then
    test_pass "Backend container is running"
else
    test_fail "Backend container is not running"
fi

if docker-compose ps | grep -q "mysql.*Up"; then
    test_pass "MySQL container is running"
else
    test_fail "MySQL container is not running"
fi

if docker-compose ps | grep -q "redis.*Up"; then
    test_pass "Redis container is running"
else
    test_fail "Redis container is not running"
fi

#===============================================================================
# 2. Check API Endpoints
#===============================================================================
print_section "Checking API Endpoints..."

# Health check
if curl -s -f http://localhost:8000/api/v1/health > /dev/null 2>&1; then
    test_pass "Health endpoint responding"
else
    test_fail "Health endpoint not responding"
fi

# Categories endpoint
if curl -s -f http://localhost:8000/api/v1/catalog/categories > /dev/null 2>&1; then
    test_pass "Categories endpoint responding"
else
    test_fail "Categories endpoint not responding"
fi

# Products endpoint
if curl -s -f http://localhost:8000/api/v1/catalog/products > /dev/null 2>&1; then
    test_pass "Products endpoint responding"
else
    test_fail "Products endpoint not responding"
fi

# Promotions endpoint
if curl -s -f http://localhost:8000/api/v1/promotions > /dev/null 2>&1; then
    test_pass "Promotions endpoint responding"
else
    test_fail "Promotions endpoint not responding"
fi

#===============================================================================
# 3. Check Database
#===============================================================================
print_section "Checking Database..."

# Check tables exist
TABLES=$(docker-compose exec -T backend php artisan db:show --json 2>/dev/null | grep -o '"table_count":[0-9]*' | cut -d':' -f2)
if [ "$TABLES" -gt 10 ]; then
    test_pass "Database has $TABLES tables"
else
    test_fail "Database has only $TABLES tables (expected 15+)"
fi

# Check products seeded
PRODUCTS=$(docker-compose exec -T mysql mysql -u root -ppassword ichri -se "SELECT COUNT(*) FROM products" 2>/dev/null)
if [ "$PRODUCTS" -gt 50 ]; then
    test_pass "Database has $PRODUCTS products"
else
    test_warn "Database has only $PRODUCTS products"
fi

# Check users seeded
USERS=$(docker-compose exec -T mysql mysql -u root -ppassword ichri -se "SELECT COUNT(*) FROM users" 2>/dev/null)
if [ "$USERS" -gt 0 ]; then
    test_pass "Database has $USERS users"
else
    test_fail "Database has no users"
fi

#===============================================================================
# 4. Check Redis Connection
#===============================================================================
print_section "Checking Redis..."

if docker-compose exec -T redis redis-cli ping > /dev/null 2>&1; then
    test_pass "Redis is responding"
else
    test_fail "Redis is not responding"
fi

#===============================================================================
# 5. Check File Structure
#===============================================================================
print_section "Checking File Structure..."

# Backend files
if [ -f "backend/.env" ]; then
    test_pass "Backend .env exists"
else
    test_fail "Backend .env missing"
fi

if [ -f "backend/composer.json" ]; then
    test_pass "Backend composer.json exists"
else
    test_fail "Backend composer.json missing"
fi

# Mobile files
if [ -f "mobile/package.json" ]; then
    test_pass "Mobile package.json exists"
else
    test_warn "Mobile package.json missing"
fi

# Web files
if [ -f "web/package.json" ]; then
    test_pass "Web package.json exists"
else
    test_warn "Web package.json missing"
fi

#===============================================================================
# 6. Check Documentation
#===============================================================================
print_section "Checking Documentation..."

DOCS=(
    "README.md"
    "QUICKSTART.md"
    "API_DOCUMENTATION.md"
    "COMPLETE_CHECKLIST.md"
    "PRODUCTION_DEPLOY.md"
    "PROJECT_COMPLETE.md"
    "FINAL_SUMMARY.md"
)

for doc in "${DOCS[@]}"; do
    if [ -f "$doc" ]; then
        test_pass "$doc exists"
    else
        test_fail "$doc missing"
    fi
done

#===============================================================================
# 7. Run Tests
#===============================================================================
print_section "Running Automated Tests..."

if docker-compose exec -T backend php artisan test --stop-on-failure > /dev/null 2>&1; then
    test_pass "All tests passing"
else
    test_fail "Some tests are failing"
fi

#===============================================================================
# Results Summary
#===============================================================================
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}   Verification Results${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}✓ Passed:${NC}   $PASSED"
echo -e "  ${RED}✗ Failed:${NC}   $FAILED"
echo -e "  ${YELLOW}⚠ Warnings:${NC} $WARNINGS"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}   ✓ ALL CHECKS PASSED!${NC}"
    echo -e "${GREEN}   ichri.tn is ready to go! 🚀${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED}   ✗ SOME CHECKS FAILED!${NC}"
    echo -e "${RED}   Please fix the issues above${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    exit 1
fi
