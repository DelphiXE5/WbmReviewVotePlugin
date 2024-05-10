<?php declare(strict_types=1);

namespace Wbm;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class WbmReviewVotePlugin extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
{
    parent::uninstall($uninstallContext);

    if ($uninstallContext->keepUserData()) {
        return;
    }

    $uninstallContext->getMigrationCollection()->migrateDestructiveInPlace();
    // Remove or deactivate the data created by the plugin
}
}