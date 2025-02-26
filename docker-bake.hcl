variable "REGISTRY" {
    default = "ambroisemaupate/intervention-request"
}

variable "VERSION" {
    default = "5.0.0"
}

target "intervention" {
    name = "intervention-${item.name}"
    platforms = ["linux/amd64"]
    matrix = {
        item = [
            {
                name = "php"
                target = "php-prod"
            },
        ]
    }
    context = "."
    target = item.target
    dockerfile = "Dockerfile"
    tags = ["${REGISTRY}:${VERSION}", "${REGISTRY}:latest"]
}
