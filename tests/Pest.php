<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in('Unit');
uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
