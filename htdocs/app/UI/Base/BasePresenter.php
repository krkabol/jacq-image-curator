<?php

declare(strict_types=1);

namespace app\UI\Base;
use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
const DESTINATION_AFTER_SIGN_IN = "Curator:";
const DESTINATION_AFTER_SIGN_OUT = "Home:";
}
