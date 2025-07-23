variable "REGISTRY" {
    default = "ambroisemaupate/intervention-request"
}

variable "VERSION" {
    default = "7.0.0"
}

group "default" {
    targets = ["intervention", "intervention-frankenphp"]
}

target "intervention" {
    name = "intervention-${item.name}"
    platforms = ["linux/amd64", "linux/arm64"]
    matrix = {
        item = [
            {
                name = "php"
                target = "php-prod"
            },
            {
                name = "frankenphp"
                target = "frankenphp-prod"
            },
        ]
    }
    context = "."
    target = item.target
    dockerfile = "Dockerfile"
    tags = [
        notequal(VERSION, "develop") ? "${REGISTRY}:${VERSION}" : "${REGISTRY}:develop",
        notequal(VERSION, "develop") ? "${REGISTRY}:latest" : "",
    ]
}

target "intervention-frankenphp" {
    name = "intervention-${item.name}"
    platforms = ["linux/amd64", "linux/arm64"]
    matrix = {
        item = [
            {
                name = "frankenphp"
                target = "frankenphp-prod"
            },
        ]
    }
    context = "."
    target = item.target
    dockerfile = "Dockerfile"
    tags = [
        notequal(VERSION, "develop") ? "${REGISTRY}:${item.name}-${VERSION}" : "${REGISTRY}:${item.name}-develop",
        notequal(VERSION, "develop") ? "${REGISTRY}:${item.name}" : "",
    ]
}
