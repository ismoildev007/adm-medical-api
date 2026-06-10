<?php

it('tests apiIndex', function () {
    $this->getJson('/api/v1/docs')->assertStatus(404);
});

it('tests apiShow', function () {
    $this->getJson('/api/v1/docs/auth')->assertStatus(404);
});

it('tests index', function () {
    $this->get('/docs')->assertStatus(200);
});

it('tests show', function () {
    $this->get('/docs/auth')->assertStatus(200);
});
