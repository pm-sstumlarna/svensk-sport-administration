#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/podman-run.sh [image-name]
# Defaults: image-name=svensksportadministration:dev
# Runs an interactive shell, mounting current project to /app and using host networking

IMAGE_NAME="${1:-svensksportadministration:dev}"

echo "Running image $IMAGE_NAME (interactive shell) with Podman..."
podman run --rm -it --network host -v "$(pwd):/app" -w /app "$IMAGE_NAME" bash
