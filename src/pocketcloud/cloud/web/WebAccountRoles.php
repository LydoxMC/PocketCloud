<?php

namespace pocketcloud\cloud\web;

enum WebAccountRoles: string {

    case ADMIN = "admin";
    case DEFAULT = "default";

    public function roleName(): string {
        return $this->value;
    }
}