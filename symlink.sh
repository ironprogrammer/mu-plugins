#!/bin/bash
#
# MU-Plugins Symlinker
# Interactive script to symlink plugins to a WordPress site's mu-plugins directory
#
# Usage: ./symlink.sh /path/to/site/wp-content/mu-plugins

set -e

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Get destination path (with autocomplete if run interactively)
if [ -z "$1" ]; then
    # Create temporary inputrc for case-insensitive completion
    TEMP_INPUTRC=$(mktemp)
    cat > "$TEMP_INPUTRC" << 'EOF'
set completion-ignore-case on
set show-all-if-ambiguous on
EOF

    echo "Enter destination mu-plugins directory path:"
    echo "(Press TAB for autocomplete)"
    INPUTRC="$TEMP_INPUTRC" read -e -p "> " DEST
    rm -f "$TEMP_INPUTRC"

    DEST="${DEST%/}"  # Remove trailing slash if present
else
    DEST="$1"
fi

# Validate destination exists
if [ ! -d "$DEST" ]; then
    echo "Error: Destination directory does not exist: $DEST"
    exit 1
fi

echo "Source: $SOURCE_DIR"
echo "Destination: $DEST"
echo ""

# Symlink autoloader
echo "Symlinking autoloader..."
ln -sf "$SOURCE_DIR/00-autoloader.php" "$DEST/00-autoloader.php"
echo "  ✓ 00-autoloader.php"
echo ""

# Get available plugins (directories only, exclude hidden dirs)
plugins=()
while IFS= read -r dir; do
    plugins+=("$(basename "$dir")")
done < <(find "$SOURCE_DIR" -mindepth 1 -maxdepth 1 -type d -not -name ".*" | sort)

if [ ${#plugins[@]} -eq 0 ]; then
    echo "No plugins found in $SOURCE_DIR"
    exit 0
fi

# Show available plugins
echo "Available plugins:"
for i in "${!plugins[@]}"; do
    plugin="${plugins[$i]}"
    # Check if already symlinked
    if [ -L "$DEST/$plugin" ]; then
        echo "  $((i+1)). $plugin (already linked)"
    else
        echo "  $((i+1)). $plugin"
    fi
done
echo "  a. All plugins"
echo ""

# Get user selection
read -p "Select plugins (e.g., '1 3 5' or 'a' for all): " selection

echo ""

# Process selection
if [[ "$selection" == "a" ]]; then
    # Link all plugins
    echo "Linking all plugins..."
    for plugin in "${plugins[@]}"; do
        ln -sf "$SOURCE_DIR/$plugin" "$DEST/$plugin"
        echo "  ✓ $plugin"
    done
else
    # Link selected plugins
    echo "Linking selected plugins..."
    for num in $selection; do
        if [[ "$num" =~ ^[0-9]+$ ]] && [ "$num" -ge 1 ] && [ "$num" -le ${#plugins[@]} ]; then
            plugin="${plugins[$((num-1))]}"
            ln -sf "$SOURCE_DIR/$plugin" "$DEST/$plugin"
            echo "  ✓ $plugin"
        else
            echo "  ✗ Invalid selection: $num"
        fi
    done
fi

echo ""
echo "Done!"
