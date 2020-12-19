<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Column;

use WArslett\TableBuilder\Action;
use WArslett\TableBuilder\ActionBuilderInterface;
use WArslett\TableBuilder\ActionGroup;
use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class ActionGroupColumnTest extends TestCase
{

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsRenderingTypeOnTableCell()
    {
        $actionGroupColumn = ActionGroupColumn::withName('my_action_group');
        $cell = $actionGroupColumn->buildTableCell([]);
        $this->assertSame(ActionGroupColumn::class, $cell->getRenderingType());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsLabelOnHeading()
    {
        $label = 'My Label';
        $actionGroupColumn = ActionGroupColumn::withName('my_action_group')->setLabel($label);
        $heading = $actionGroupColumn->buildTableHeading();
        $this->assertSame($label, $heading->getLabel());
    }

    /**
     * @return void
     */
    public function testBuildTableCellSetsNameOnHeading()
    {
        $name = 'my_column';
        $actionGroupColumn = ActionGroupColumn::withName($name);
        $heading = $actionGroupColumn->buildTableHeading();
        $this->assertSame($name, $heading->getName());
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsValueToActionGroup()
    {
        $actionGroupColumn = ActionGroupColumn::withName('my_action_group');
        $cell = $actionGroupColumn->buildTableCell([]);
        $value = $cell->getValue();
        $this->assertInstanceOf(ActionGroup::class, $value);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellOneActionBuilderBuildsOneAction()
    {
        $actionName = 'foo';
        $action = new Action('Foo');
        $actionGroupColumn = ActionGroupColumn::withName('my_action_group')
            ->addActionBuilder($this->mockActionBuilder($actionName, true, $action))
        ;
        $cell = $actionGroupColumn->buildTableCell([]);

        /** @var ActionGroup $value */
        $value = $cell->getValue();
        $actions = $value->getActions();

        $this->assertSame(1, count($actions));
        $this->assertArrayHasKey($actionName, $actions);
        $this->assertSame($action, $actions[$actionName]);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellActionBuilderConditionDisallowedExcludesAction()
    {
        $actionName = 'foo';
        $action = new Action('Foo');
        $actionGroupColumn = ActionGroupColumn::withName('my_action_group')
            ->addActionBuilder($this->mockActionBuilder($actionName, false, $action))
        ;
        $cell = $actionGroupColumn->buildTableCell([]);

        /** @var ActionGroup $value */
        $value = $cell->getValue();
        $actions = $value->getActions();

        $this->assertEmpty($actions);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellTwoActionBuilderBuildsTwoAction()
    {
        $actionGroupColumn = ActionGroupColumn::withName('my_action_group')
            ->addActionBuilder($this->mockActionBuilder('foo', true, new Action('Foo')))
            ->addActionBuilder($this->mockActionBuilder('bar', true, new Action('Bar')))
        ;
        $cell = $actionGroupColumn->buildTableCell([]);

        /** @var ActionGroup $value */
        $value = $cell->getValue();
        $actions = $value->getActions();

        $this->assertSame(2, count($actions));
    }

    /**
     * @param string $actionName
     * @param bool $isAllowed
     * @param Action $action
     * @return ActionBuilderInterface&Mock
     */
    private function mockActionBuilder(string $actionName, bool $isAllowed, Action $action): ActionBuilderInterface
    {
        $actionBuilder = m::mock(ActionBuilderInterface::class);
        $actionBuilder->shouldReceive('getName')->andReturn($actionName);
        $actionBuilder->shouldReceive('isAllowedFor')->andReturn($isAllowed);
        $actionBuilder->shouldReceive('buildAction')->andReturn($action);
        return $actionBuilder;
    }
}
