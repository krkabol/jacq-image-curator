<?php declare(strict_types = 1);

namespace App\UI\Base\Accessory;

use Latte\Extension;
use Nette\Utils\Html;

final class LatteExtension extends Extension
{

    /**
     * @return array|callable[]
     */
    public function getFilters(): array
    {
        return ['status' => [$this, 'status']];
    }

    /**
     * @return array|callable[]
     */
    public function getFunctions(): array
    {
        return [];
    }

    public function status(mixed $status): Html
    {
        $el = Html::el('b');
        if ((bool) $status === true) {
            $el->style['color'] = 'green';
            $el->setText('✓');
        } else {
            $el->style['color'] = 'red';
            $el->setText('𐄂');
        }

        return $el;
    }

}
