<?php

namespace Spirit\Structure;

abstract class Migration
{

    abstract public function up();

    abstract public function down();

}