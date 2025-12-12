#!/bin/bash

# Cheapo Canonical Validation Script
# Tests that canonical tags are properly added/avoided

# Check if site URL is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <site-url>"
    echo "Example: $0 http://example.test"
    exit 1
fi

SITE_URL="$1"
FAILED=0
PASSED=0

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "Testing Cheapo Canonical Plugin at $SITE_URL"
echo "=============================================="
echo ""

# Test function
test_canonical() {
    local url=$1
    local expected_count=$2
    local page_type=$3
    local should_be_from=$4

    echo -n "Testing $page_type ($url)... "

    # Get the page content
    page_content=$(curl -s "$url")

    # Get canonical count
    count=$(echo "$page_content" | grep -c 'rel="canonical"')

    if [ "$count" -eq "$expected_count" ]; then
        echo -e "${GREEN}✓ PASS${NC} (found $count canonical tag)"
        PASSED=$((PASSED + 1))

        # Show the canonical tag
        canonical=$(echo "$page_content" | grep -i "canonical" | sed 's/^[[:space:]]*//')
        echo "  → $canonical"
        echo "  → Source: $should_be_from"
    else
        echo -e "${RED}✗ FAIL${NC} (found $count canonical tags, expected $expected_count)"
        FAILED=$((FAILED + 1))

        # Show all canonical tags found
        echo "  Found tags:"
        echo "$page_content" | grep -i "canonical" | sed 's/^/    /'
    fi
    echo ""
}

# Get test URLs dynamically
echo -e "${YELLOW}Fetching test URLs from WordPress API...${NC}"
POST_URL=$(curl -s "${SITE_URL}/wp-json/wp/v2/posts?per_page=1" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data[0]['link'] if data else '')" 2>/dev/null)
PAGE_URL=$(curl -s "${SITE_URL}/wp-json/wp/v2/pages?per_page=1" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data[0]['link'] if data else '')" 2>/dev/null)
CATEGORY_URL=$(curl -s "${SITE_URL}/wp-json/wp/v2/categories?per_page=1" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data[0]['link'] if data else '')" 2>/dev/null)

echo ""

# Run tests
test_canonical "$SITE_URL/" 1 "Homepage" "Plugin (cheapo-canonical)"

if [ -n "$POST_URL" ]; then
    test_canonical "$POST_URL" 1 "Single Post" "WordPress core (rel_canonical)"
else
    echo -e "${YELLOW}⚠ Skipping single post test (no posts found)${NC}"
    echo ""
fi

if [ -n "$PAGE_URL" ]; then
    test_canonical "$PAGE_URL" 1 "Single Page" "WordPress core (rel_canonical)"
else
    echo -e "${YELLOW}⚠ Skipping single page test (no pages found)${NC}"
    echo ""
fi

if [ -n "$CATEGORY_URL" ]; then
    test_canonical "$CATEGORY_URL" 1 "Category Archive" "Plugin (cheapo-canonical)"
else
    echo -e "${YELLOW}⚠ Skipping category archive test (no categories found)${NC}"
    echo ""
fi

# Test date archive
test_canonical "${SITE_URL}/2025/" 1 "Date Archive (Year)" "Plugin (cheapo-canonical)"

# Test paginated archive
test_canonical "${SITE_URL}/page/2/" 1 "Paginated Homepage" "Plugin (cheapo-canonical)"

# Test paginated category archive
test_canonical "${SITE_URL}/category/test/" 1 "Category Archive Page 1" "Plugin (cheapo-canonical)"
test_canonical "${SITE_URL}/category/test/page/2/" 1 "Category Archive Page 2" "Plugin (cheapo-canonical)"

# Summary
echo "=============================================="
echo -e "Results: ${GREEN}$PASSED passed${NC}, ${RED}$FAILED failed${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}All tests passed! ✓${NC}"
    exit 0
else
    echo -e "${RED}Some tests failed! ✗${NC}"
    exit 1
fi
