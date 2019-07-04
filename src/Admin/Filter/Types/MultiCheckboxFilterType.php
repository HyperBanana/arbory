<?php


namespace Arbory\Base\Admin\Filter\Types;


use Arbory\Base\Admin\Filter\Config\SelectLikeTypeConfig;
use Arbory\Base\Admin\Filter\FilterItem;
use Arbory\Base\Admin\Filter\FilterTypeInterface;
use Arbory\Base\Exceptions\BadMethodCallException;
use Arbory\Base\Html\Html;

class MultiCheckboxFilterType extends AbstractType implements FilterTypeInterface
{
    /**
     * @var SelectLikeTypeConfig
     */
    protected $config;

    /**
     * @var array
     */
    protected $value;

    /**
     * @param FilterItem $filterItem
     * @return mixed
     * @throws BadMethodCallException
     */
    public function render(FilterItem $filterItem)
    {
        $options = $this->config->getOptions() ?? [];

        $labels = [];

        foreach($options as $key => $value) {
            $checked = in_array($key, (array) $this->getValue(), false);

            $labels[] = Html::label([
                Html::input((string) $value)
                    ->setType('checkbox')
                    ->addAttributes(['value' => $key])
                    ->addAttributes($checked ? ['checked' => true] : [])
                    ->setName($filterItem->getNamespacedName() . '[]'),
            ]);
        }

        return Html::div($labels)->addClass('checkbox');
    }

    public function getConfigType(): ?string
    {
        return SelectLikeTypeConfig::class;
    }
}