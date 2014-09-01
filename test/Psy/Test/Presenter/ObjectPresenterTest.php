<?php

/*
 * This file is part of Psy Shell
 *
 * (c) 2012-2014 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Test\Presenter;

use Psy\Presenter\ArrayPresenter;
use Psy\Presenter\ObjectPresenter;
use Psy\Presenter\Presenter;
use Psy\Presenter\PresenterManager;
use Psy\Presenter\ScalarPresenter;
use Psy\Test\Presenter\Fixtures\SimpleClass;

class ObjectPresenterTest extends \PHPUnit_Framework_TestCase
{
    private $presenter;
    private $manager;

    public function setUp()
    {
        $this->presenter = new ObjectPresenter();

        $this->manager   = new PresenterManager();
        $this->manager->addPresenter(new ScalarPresenter());
        $this->manager->addPresenter(new ArrayPresenter());
        $this->manager->addPresenter($this->presenter);
    }

    public function testPresentEmptyObject()
    {
        $empty = new \StdClass();
        $this->assertEquals(
            $this->presenter->presentRef($empty) . ' {}',
            $this->presenter->present($empty)
        );
    }

    public function testPresentWithDepth()
    {
        $obj = new \StdClass();
        $obj->name = 'std';
        $obj->type = 'class';
        $obj->tags = array('stuff', 'junk');
        $obj->child = new \StdClass();
        $obj->child->name = 'std, jr';

        $hash      = spl_object_hash($obj);
        $childHash = spl_object_hash($obj->child);

        $expected = <<<EOS
\<stdClass #$hash> {
    name: "std",
    type: "class",
    tags: Array(2),
    child: \<stdClass #$childHash>
}
EOS;

        $this->assertStringMatchesFormat($expected, $this->presenter->present($obj, 1));
    }

    public function testPresentWithoutDepth()
    {
        $obj = new \StdClass();
        $obj->name = 'std';
        $obj->type = 'class';
        $obj->tags = array('stuff', 'junk');
        $obj->child = new \StdClass();
        $obj->child->name = 'std, jr';

        $hash      = spl_object_hash($obj);
        $childHash = spl_object_hash($obj->child);

        $expected = <<<EOS
\<stdClass #$hash> {
    name: "std",
    type: "class",
    tags: [
        "stuff",
        "junk"
    ],
    child: \<stdClass #$childHash> {
        name: "std, jr"
    }
}
EOS;

        $this->assertStringMatchesFormat($expected, $this->presenter->present($obj));
    }

    public function testPresentRef()
    {
        $obj = new \StdClass();

        $formatted = $this->presenter->presentRef($obj);

        $this->assertStringMatchesFormat('\<stdClass #%s>', $formatted);
        $this->assertContains(spl_object_hash($obj), $formatted);
    }

    public function testPresentVerbose()
    {
        $obj = new SimpleClass();
        $hash = spl_object_hash($obj);

        $expected = <<<EOS
\<Psy\Test\Presenter\Fixtures\SimpleClass #$hash> {
    hello: "Hello world!",
    foo: "bar",
    secret: 42
}
EOS;

        $this->assertStringMatchesFormat($expected, $this->presenter->present($obj, null, false, Presenter::VERBOSE));
    }
}
