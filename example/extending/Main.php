<?php
/**
 * User: albertsultanov
 * Date: 27.06.17
 * Time: 0:23
 */

namespace vivace\di\example\extending;


use vivace\di\example\extending\service\ViewInterface;
use vivace\di\Scope\Package;

class Main extends Package
{
    public function __construct()
    {
        $this->use(new module\blog\Main())
            ->as(ViewInterface::class, 'blogView');

        $this->use(new module\admin\Main())
            ->as(ViewInterface::class, 'adminView');
    }

    public function boot()
    {
        /** @var ViewInterface $view */
        $view = $this->import(ViewInterface::class);
        /** @var ViewInterface $adminView */
        $adminView = $this->import('adminView');
        /** @var ViewInterface $blogView */
        $blogView = $this->import('blogView');

        assert($blogView === $view);
        assert($adminView->getTpl() === 'admin');
        assert($blogView->getTpl() === 'blog');

        return "OK\n";
    }
}