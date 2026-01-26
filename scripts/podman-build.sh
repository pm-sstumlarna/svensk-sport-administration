#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/podman-build.sh [image-name] [context]
# Defaults: image-name=svensksportadministration:dev  context=.

IMAGE_NAME="${1:-svensksportadministration:dev}"
CONTEXT="${2:-.}"

echo "Building image $IMAGE_NAME from $CONTEXT using Podman..."
podman build --pull --rm -f dev-container.dockerfile -t "$IMAGE_NAME" "$CONTEXT"
echo "Built $IMAGE_NAME"
