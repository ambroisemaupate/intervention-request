variable "REGISTRY" {
    default = "ambroisemaupate/intervention-request"
}

variable "VERSION" {
    default = "develop"
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
        notequal(VERSION, "develop") && notequal(item.name, "php") ? "${REGISTRY}:${item.name}-${VERSION}" : "",
        notequal(VERSION, "develop") && notequal(item.name, "php") ? "${REGISTRY}:${item.name}" : "",
        equal(VERSION, "develop") && notequal(item.name, "php") ? "${REGISTRY}:${item.name}-develop" : "",
        equal(VERSION, "develop") && equal(item.name, "php") ? "${REGISTRY}:develop" : "",
        notequal(VERSION, "develop") && equal(item.name, "php") ? "${REGISTRY}:${VERSION}" : "",
        notequal(VERSION, "develop") && equal(item.name, "php") ? "${REGISTRY}:latest" : "",
    ]
}
