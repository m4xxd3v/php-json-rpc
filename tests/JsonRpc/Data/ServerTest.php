<?php

/**
 * Copyright (C) 2015 Datto, Inc.
 *
 * This file is part of PHP JSON-RPC.
 *
 * PHP JSON-RPC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * PHP JSON-RPC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PHP JSON-RPC. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Spencer Mortensen <smortensen@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2015 Datto, Inc.
 */

namespace Datto\JsonRpc\Message;

use PHPUnit_Framework_TestCase;
use Datto\Tests\Example\Method;

class ServerTest extends PHPUnit_Framework_TestCase
{
    public function testArgumentsPositionalA()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract", "params": [3, 2], "id": 1}';

        $output = '{"jsonrpc": "2.0", "result": 1, "id": 1}';

        $this->compare($input, $output);
    }

    public function testArgumentsPositionalB()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract", "params": [2, 3], "id": 1}';

        $output = '{"jsonrpc": "2.0", "result": -1, "id": 1}';

        $this->compare($input, $output);
    }

    public function testArgumentsNamedA()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract", "params": {"minuend": 3, "subtrahend": 2}, "id": 1}';

        $output = '{"jsonrpc": "2.0", "result": 1, "id": 1}';

        $this->compare($input, $output);
    }

    public function testArgumentsInvalid()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract", "params": [], "id": 1}';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32602, "message": "Invalid params"}, "id": "1"}';

        $this->compare($input, $output);
    }

    public function testArgumentsNamedB()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract", "params": {"subtrahend": 2, "minuend": 3}, "id": 1}';

        $output = '{"jsonrpc": "2.0", "result": 1, "id": 1}';

        $this->compare($input, $output);
    }

    public function testNotificationArguments()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract", "params": [3, 2]}';

        $output = 'null';

        $this->compare($input, $output);
    }

    public function testNotification()
    {
        $input = '{"jsonrpc": "2.0", "method": "Math/subtract"}';

        $output = 'null';

        $this->compare($input, $output);
    }

    public function testUndefinedMethod()
    {
        $input ='{"jsonrpc": "2.0", "method": "Math/undefined", "id": "1"}';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}';

        $this->compare($input, $output);
    }

    public function testInvalidJson()
    {
        $input = '{"jsonrpc": "2.0", "method": "foobar", "params": "bar", "baz]';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}';

        $this->compare($input, $output);
    }

    public function testInvalidMethod()
    {
        $input = '{"jsonrpc": "2.0", "method": 1, "params": [1, 2]}';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}';

        $this->compare($input, $output);
    }

    public function testInvalidParams()
    {
        $input = '{"jsonrpc": "2.0", "method": "foobar", "params": "bar"}';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}';

        $this->compare($input, $output);
    }

    public function testInvalidId()
    {
        $input = '{"jsonrpc": "2.0", "method": "foobar", "params": [1, 2], "id": [1]}';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}';

        $this->compare($input, $output);
    }

    public function testBatchInvalidJson()
    {
        $input = ' [
            {"jsonrpc": "2.0", "method": "Math/subtract", "params": [1, 2, 4], "id": "1"},
            {"jsonrpc": "2.0", "method"
        ]';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}';

        $this->compare($input, $output);
    }

    public function testBatchEmpty()
    {
        $input = '[
        ]';

        $output = '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}';

        $this->compare($input, $output);
    }

    public function testBatchInvalidElement()
    {
        $input = '[
            1
        ]';

        $output = '[
            {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
        ]';

        $this->compare($input, $output);
    }

    public function testBatchInvalidElements()
    {
        $input = '[
            1,
            2,
            3
        ]';

        $output = '[
            {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
            {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
            {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
        ]';

        $this->compare($input, $output);
    }

    public function testBatch()
    {
        $input = '[
            {"jsonrpc": "2.0", "method": "Math/subtract", "params": [1, -1], "id": "1"},
            {"jsonrpc": "2.0", "method": "Math/subtract", "params": [1, -1]},
            {"foo": "boo"},
            {"jsonrpc": "2.0", "method": "undefined", "params": {"name": "myself"}, "id": "5"}
        ]';

        $output = '[
            {"jsonrpc": "2.0", "result": 2, "id": "1"},
            {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null},
            {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"}
        ]';

        $this->compare($input, $output);
    }

    public function testBatchNotifications()
    {
        $input = '[
            {"jsonrpc": "2.0", "method": "Math/subtract", "params": [4, 2]},
            {"jsonrpc": "2.0", "method": "Math/subtract", "params": [3, 7]}
        ]';

        $output = 'null';

        $this->compare($input, $output);
    }

    private function compare($input, $expectedJsonOutput)
    {
        $method = new Method();
        $server = new Server($method);

        $actualJsonOutput = $server->reply($input);

        $expectedOutput = json_decode($expectedJsonOutput, true);
        $actualOutput = json_decode($actualJsonOutput, true);

        $this->assertEquals($expectedOutput, $actualOutput);
    }
}
