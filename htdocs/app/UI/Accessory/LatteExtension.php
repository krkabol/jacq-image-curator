<?php

declare(strict_types=1);

namespace app\UI\Accessory;

use Latte\Extension;
use Nette\Utils\Html;


final class LatteExtension extends Extension
{
	public function getFilters(): array
	{
		return ["status"=>[$this,"status"]];
	}


	public function getFunctions(): array
	{
		return [];
	}

    public function status($status)
    {
        $el = Html::el("b");
        if ($status == true) {
            $el->style['color'] = 'green';
            $el->setText("✓");
        }else{
            $el->style['color'] = 'red';
            $el->setText("𐄂");
        }
        return $el;
    }
}
