variable "TAG" {
    default = "4.1.2"
}

variable "IMAGE" {
    default = "ambroisemaupate/intervention-request"
}

group "default" {
    targets = ["intervention"]
}

target "intervention" {
    context = "."
    dockerfile = "Dockerfile"
    platforms = ["linux/amd64", "linux/arm64/v8"]
    tags = ["${IMAGE}:${TAG}"]
}
