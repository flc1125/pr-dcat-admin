<?php

namespace Dcat\Admin\Widgets;

use Dcat\Admin\Support\Helper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class Tree extends Widget
{
    protected $view = 'admin::widgets.tree';

    protected $options = [
        'plugins' => ['checkbox', 'types'],
        'core'    => [
            'check_callback' => true,

            'themes' => [
                'name'       => 'proton',
                'responsive' => true,
            ],
        ],
        'checkbox' => [
            'keep_selected_style' => false,
        ],
        'types' => [
            'default' => [
                'icon' => false,
            ],
        ],
    ];

    protected $id;

    protected $columnNames = [
        'id'     => 'id',
        'text'   => 'name',
        'parent' => 'parent_id',
    ];

    protected $nodes = [];

    protected $value = [];

    protected $checkAll = false;

    public function __construct($nodes = [])
    {
        $this->nodes($nodes);

        $this->id = 'widget-tree-'.Str::random(8);
    }

    public function checkAll()
    {
        $this->checkAll = true;

        return $this;
    }

    public function check($value)
    {
        $this->value = Helper::array($value);

        return $this;
    }

    public function setIdColumn(string $name)
    {
        $this->columnNames['id'] = $name;

        return $this;
    }

    public function setTitleColumn(string $name)
    {
        $this->columnNames['text'] = $name;

        return $this;
    }

    public function setParentColumn(string $name)
    {
        $this->columnNames['parent'] = $name;

        return $this;
    }

    /**
     * @param  array  $data  exp:
     *                       {
     *                       "id": "1",
     *                       "parent": "#",
     *                       "text": "Dashboard",
     *                       // "state": {"selected": true}
     *                       }
     * @param  array  $data
     * @return $this
     */
    public function nodes($data)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        $this->nodes = &$data;

        return $this;
    }

    public function render()
    {
        $this->id($this->id);
        $this->class('jstree-wrapper');
        $this->defaultHtmlAttribute('style', 'border:0;padding:5px 0');

        $this->formatNodes();

        $this->variables = [
            'id'    => $this->id,
            'nodes' => &$this->nodes,
        ];

        return parent::render(); // TODO: Change the autogenerated stub
    }

    protected function formatNodes()
    {
        $value = $this->value;
        if ($value && ! is_array($value)) {
            $value = explode(',', $value);
        }
        $value = (array) $value;

        if (! $this->nodes) {
            return;
        }

        $idColumn     = $this->columnNames['id'];
        $textColumn   = $this->columnNames['text'];
        $parentColumn = $this->columnNames['parent'];

        $nodes = [];

        foreach ($this->nodes as &$v) {
            if (empty($v[$idColumn])) {
                continue;
            }

            $parentId = $v[$parentColumn] ?? '#';
            if (empty($parentId)) {
                $parentId = '#';
            }

            $v['state'] = [];

            if ($this->checkAll || ($value && in_array($v[$idColumn], $value))) {
                $v['state']['selected'] = true;
            }

            $v['state']['disabled'] = true;

            $nodes[] = [
                'id'     => $v[$idColumn],
                'text'   => $v[$textColumn] ?? null,
                'parent' => $parentId,
                'state'  => $v['state'],
            ];
        }

        $this->nodes = &$nodes;
    }
}
