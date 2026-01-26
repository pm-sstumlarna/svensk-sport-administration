#!/usr/bin/env bash
set -euo pipefail

# Usage: ./scripts/podman-run.sh [image-name] [command...]
# Defaults: image-name=svensksportadministration:dev, command=bash
# Runs a container, mounting current project to /app and using host networking

IMAGE_NAME="${1:-svensksportadministration:dev}"
shift || true

if [ $# -eq 0 ]; then
    set -- "bash"
fi

echo "Running image $IMAGE_NAME with command: $@"
podman run --rm -it --network host -v "$(pwd):/app:Z" -w /app "$IMAGE_NAME" "$@"
