<?php declare(strict_types=1);

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
        return ['status' => [$this, 'status'], 'email' => [$this, 'email']];
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
        if ((bool)$status === true) {
            $el->style('color', 'green');
            $el->setText('✓');
        } else {
            $el->style('color', 'red');
            $el->setText('𐄂');
        }

        return $el;
    }

    public function email(string $email): Html
    {
       $link = Html::el('a')
            ->href('mailto:'.$email)
            ->setText($email);

        $icon = Html::el('i')
            ->class('fa fa-envelope')
            ->aria('hidden', 'true');
        $span = Html::el('span')
            ->addHtml($link)
            ->addHtml(' ')
            ->addHtml($icon);

        return $span;
    }

}
