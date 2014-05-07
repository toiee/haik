<?php

use Hokuken\Haik\Page\YamlPageMeta;
use Symfony\Component\Yaml\Yaml;

class YamlPageMetaTest extends PHPUnit_Framework_TestCase {

    protected $testData;

    public function setUp()
    {
        $this->page = $page = 'FrontPage';
        $this->yamlFile = $yaml_file = './tests/data/page_meta.yml';
        $this->testData = array(
            'title' => 'TestTitle',
            'template_name' => 'top',
            'description' => "multi\nline\ndescription",
            'group' => array(
                'key' => 'value'
            )
        );
        $yaml = Yaml::dump($this->testData);
        file_put_contents($yaml_file, $yaml);

        $mock = Mockery::mock('Hokuken\Haik\Page\YamlPageMeta[getFilePath]', array($page, false));
        $mock->shouldReceive('getFilePath')->andReturn($yaml_file);
        $this->pageMeta = $mock;
    }

    public function setUpData()
    {
        $this->pageMeta->setAll($this->pageMeta->read(), true);
    }

    public function testConstructor()
    {
        $mock = Mockery::mock('Hokuken\Haik\Page\YamlPageMeta[getFilePath]', array($this->page, false), function($mock)
        {
            $mock->shouldReceive('getFilePath')->andReturn($this->yamlFile);
            return $mock;
        });
        $this->assertEquals($this->testData, $mock->getAll());
    }

    public function testRead()
    {
        $data = $this->pageMeta->read();
        $this->assertEquals($this->testData, $data);
    }

    public function testGet()
    {
        $this->setUpData();

        $value = $this->pageMeta->get('title');
        $this->assertEquals('TestTitle', $value);

        $value = $this->pageMeta->get('unknown');
        $this->assertNull($value);

        $value = $this->pageMeta->get('unknown', 'foobar');
        $this->assertEquals('foobar', $value);

        $value = $this->pageMeta->get('group.key');
        $this->assertEquals('value', $value);

        $value = $this->pageMeta->get('group.unknown');
        $this->assertNull($value);
        
        $value = $this->pageMeta->get('unknowngroup.unknown');
        $this->assertNull($value);
    }

    public function testGetAll()
    {
        $this->setUpData();

        $data = $this->pageMeta->getAll();
        $this->assertEquals($this->testData, $data);
    }

    public function testSet()
    {
        $expected = 'test title';
        $this->pageMeta->set('title', $expected);
        $value = $this->pageMeta->get('title');
        $this->assertEquals($expected, $value);

        $expected = 'grouped value';
        $this->pageMeta->set('group.value', $expected);
        $value = $this->pageMeta->get('group.value');
        $this->assertEquals($expected, $value);

        $expected = 'new value';
        $this->pageMeta->set('newkey', $expected);
        $value = $this->pageMeta->get('newkey');
        $this->assertEquals($expected, $value);

        $expected = 'more.nested.value';
        $this->pageMeta->set('more.nested.value', $expected);
        $value = $this->pageMeta->get('more.nested.value');
        $this->assertEquals($expected, $value);

        $expected = array('nested.value' => 'more.nested.value');
        $value = $this->pageMeta->get('more');
        $this->assertEquals($expected, $value);
    }

    public function testSetAll()
    {
        $this->pageMeta->setAll($this->testData);
        $this->assertAttributeEquals($this->testData, 'data', $this->pageMeta);
    }

    public function testSave()
    {
        $this->setUpData();
        $this->pageMeta->set('newkey', 'new value');
        $this->pageMeta->set('title', 'UpdatedTitle');
        $this->pageMeta->save();

        $expected = $this->testData;
        $expected['newkey'] = 'new value';
        $expected['title'] = 'UpdatedTitle';

        $data = $this->pageMeta->read();
        
        $this->assertEquals($expected, $data);
    }

    public function testDelete()
    {
        $this->pageMeta->delete();
        $data = $this->pageMeta->getAll();
        $this->assertEquals(array(), $data);

        $data = $this->pageMeta->read();
        $this->assertEquals(array(), $data);
    }

}
