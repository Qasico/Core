<?php

use Mockery as m;
use Qasico\Rubricate\Collection;
use Qasico\Support\Collection as BaseCollection;

class RubricateCollectionTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testAddingItemsToCollection()
    {
        $c = new Collection(['foo']);
        $c->add('bar')->add('baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $c->all());
    }

    public function testGettingMaxItemsFromCollection()
    {
        $c = new Collection([(object) ['foo' => 10], (object) ['foo' => 20]]);
        $this->assertEquals(20, $c->max('foo'));
    }

    public function testGettingMinItemsFromCollection()
    {
        $c = new Collection([(object) ['foo' => 10], (object) ['foo' => 20]]);
        $this->assertEquals(10, $c->min('foo'));
    }

    public function testContainsIndicatesIfModelInArray()
    {
        $mockModel = m::mock('Qasico\Rubricate\Model');
        $mockModel->shouldReceive('getKey')->andReturn(1);
        $mockModel2 = m::mock('Qasico\Rubricate\Model');
        $mockModel2->shouldReceive('getKey')->andReturn(2);
        $mockModel3 = m::mock('Qasico\Rubricate\Model');
        $mockModel3->shouldReceive('getKey')->andReturn(3);
        $c = new Collection([$mockModel, $mockModel2]);

        $this->assertTrue($c->contains($mockModel));
        $this->assertTrue($c->contains($mockModel2));
        $this->assertFalse($c->contains($mockModel3));
    }

    public function testContainsIndicatesIfKeyedModelInArray()
    {
        $mockModel = m::mock('Qasico\Rubricate\Model');
        $mockModel->shouldReceive('getKey')->andReturn('1');
        $c          = new Collection([$mockModel]);
        $mockModel2 = m::mock('Qasico\Rubricate\Model');
        $mockModel2->shouldReceive('getKey')->andReturn('2');
        $c->add($mockModel2);

        $this->assertTrue($c->contains(1));
        $this->assertTrue($c->contains(2));
        $this->assertFalse($c->contains(3));
    }

    public function testContainsKeyAndValueIndicatesIfModelInArray()
    {
        $mockModel1 = m::mock('Qasico\Rubricate\Model');
        $mockModel1->shouldReceive('offsetExists')->with('name')->andReturn(true);
        $mockModel1->shouldReceive('offsetGet')->with('name')->andReturn('Foo');
        $mockModel2 = m::mock('Qasico\Rubricate\Model');
        $mockModel2->shouldReceive('offsetExists')->andReturn(true);
        $mockModel2->shouldReceive('offsetGet')->with('name')->andReturn('Bar');
        $c = new Collection([$mockModel1, $mockModel2]);

        $this->assertTrue($c->contains('name', 'Foo'));
        $this->assertTrue($c->contains('name', 'Bar'));
        $this->assertFalse($c->contains('name', 'Bazz'));
    }

    public function testContainsClosureIndicatesIfModelInArray()
    {
        $mockModel1 = m::mock('Qasico\Rubricate\Model');
        $mockModel1->shouldReceive('getKey')->andReturn(1);
        $mockModel2 = m::mock('Qasico\Rubricate\Model');
        $mockModel2->shouldReceive('getKey')->andReturn(2);
        $c = new Collection([$mockModel1, $mockModel2]);

        $this->assertTrue($c->contains(function ($k, $m) { return $m->getKey() < 2; }));
        $this->assertFalse($c->contains(function ($k, $m) { return $m->getKey() > 2; }));
    }

    public function testFindMethodFindsModelById()
    {
        $mockModel = m::mock('Qasico\Rubricate\Model');
        $mockModel->shouldReceive('getKey')->andReturn(1);
        $c = new Collection([$mockModel]);

        $this->assertSame($mockModel, $c->find(1));
        $this->assertSame('foo', $c->find(2, 'foo'));
    }

    public function testCollectionDictionaryReturnsModelKeys()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn(2);

        $three = m::mock('Qasico\Rubricate\Model');
        $three->shouldReceive('getKey')->andReturn(3);

        $c = new Collection([$one, $two, $three]);

        $this->assertEquals([1, 2, 3], $c->modelKeys());
    }

    public function testCollectionMergesWithGivenCollection()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn(2);

        $three = m::mock('Qasico\Rubricate\Model');
        $three->shouldReceive('getKey')->andReturn(3);

        $c1 = new Collection([$one, $two]);
        $c2 = new Collection([$two, $three]);

        $this->assertEquals(new Collection([$one, $two, $three]), $c1->merge($c2));
    }

    public function testCollectionDiffsWithGivenCollection()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn(2);

        $three = m::mock('Qasico\Rubricate\Model');
        $three->shouldReceive('getKey')->andReturn(3);

        $c1 = new Collection([$one, $two]);
        $c2 = new Collection([$two, $three]);

        $this->assertEquals(new Collection([$one]), $c1->diff($c2));
    }

    public function testCollectionIntersectsWithGivenCollection()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn(2);

        $three = m::mock('Qasico\Rubricate\Model');
        $three->shouldReceive('getKey')->andReturn(3);

        $c1 = new Collection([$one, $two]);
        $c2 = new Collection([$two, $three]);

        $this->assertEquals(new Collection([$two]), $c1->intersect($c2));
    }

    public function testCollectionReturnsUniqueItems()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn(2);

        $c = new Collection([$one, $two, $two]);

        $this->assertEquals(new Collection([$one, $two]), $c->unique());
    }

    public function testOnlyReturnsCollectionWithGivenModelKeys()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn(2);

        $three = m::mock('Qasico\Rubricate\Model');
        $three->shouldReceive('getKey')->andReturn(3);

        $c = new Collection([$one, $two, $three]);

        $this->assertEquals(new Collection([$one]), $c->only(1));
        $this->assertEquals(new Collection([$two, $three]), $c->only([2, 3]));
    }

    public function testExceptReturnsCollectionWithoutGivenModelKeys()
    {
        $one = m::mock('Qasico\Rubricate\Model');
        $one->shouldReceive('getKey')->andReturn(1);

        $two = m::mock('Qasico\Rubricate\Model');
        $two->shouldReceive('getKey')->andReturn('2');

        $three = m::mock('Qasico\Rubricate\Model');
        $three->shouldReceive('getKey')->andReturn(3);

        $c = new Collection([$one, $two, $three]);

        $this->assertEquals(new Collection([$one, $three]), $c->except(2));
        $this->assertEquals(new Collection([$one]), $c->except([2, 3]));
    }

    public function testWithHiddenSetsHiddenOnEntireCollection()
    {
        $c = new Collection([new TestEloquentCollectionModel]);
        $c = $c->makeVisible(['hidden']);

        $this->assertEquals([], $c[0]->getHidden());
    }

    public function testNonModelRelatedMethods()
    {
        $a = new Collection([['foo' => 'bar'], ['foo' => 'baz']]);
        $b = new Collection(['a', 'b', 'c']);
        $this->assertEquals(get_class($a->pluck('foo')), BaseCollection::class);
        $this->assertEquals(get_class($a->keys()), BaseCollection::class);
        $this->assertEquals(get_class($a->collapse()), BaseCollection::class);
        $this->assertEquals(get_class($a->flatten()), BaseCollection::class);
        $this->assertEquals(get_class($a->zip(['a', 'b'], ['c', 'd'])), BaseCollection::class);
        $this->assertEquals(get_class($b->flip()), BaseCollection::class);
    }
}

class TestEloquentCollectionModel extends Qasico\Rubricate\Model
{
    protected $hidden = ['hidden'];
}