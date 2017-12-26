<?php

namespace UMA\RPC;

interface Procedure
{
    public function execute(Request $request): Response;

    public function paramSpec(): ?\stdClass;
}
