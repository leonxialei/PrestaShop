<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\PrestaShop\Adapter\Category\CommandHandler;

use Category;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\Domain\Category\Command\AddRootCategoryCommand;
use PrestaShop\PrestaShop\Core\Domain\Category\CommandHandler\AddRootCategoryHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Category\Exception\CannotAddCategoryException;
use PrestaShop\PrestaShop\Core\Domain\Category\Exception\CategoryException;
use PrestaShop\PrestaShop\Core\Domain\Category\ValueObject\CategoryId;

/**
 * Class AddRootCategoryHandler.
 */
final class AddRootCategoryHandler extends AbstractCategoryHandler implements AddRootCategoryHandlerInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     *
     * @throws CannotAddCategoryException
     */
    public function handle(AddRootCategoryCommand $command)
    {
        $category = $this->createRootCategoryFromCommand($command);

        return new CategoryId((int) $category->id);
    }

    /**
     * Creates legacy root category
     *
     * @param AddRootCategoryCommand $command
     *
     * @return Category
     */
    private function createRootCategoryFromCommand(AddRootCategoryCommand $command)
    {
        $category = new Category();
        $category->is_root_category = true;
        $category->level_depth = 1;
        $category->id_parent = $this->configuration->get('PS_ROOT_CATEGORY');
        $category->name = $command->getLocalizedNames();
        $category->link_rewrite = $command->getLocalizedLinkRewrites();
        $category->active = $command->isActive();

        if (null !== $command->getLocalizedDescriptions()) {
            $category->description = $command->getLocalizedDescriptions();
        }

        if (null !== $command->getLocalizedMetaTitles()) {
            $category->meta_title = $command->getLocalizedMetaTitles();
        }

        if (null !== $command->getLocalizedMetaDescriptions()) {
            $category->meta_description = $command->getLocalizedMetaDescriptions();
        }

        if (null !== $command->getLocalizedMetaKeywords()) {
            $category->meta_keywords = $command->getLocalizedMetaKeywords();
        }

        if (null !== $command->getAssociatedGroupIds()) {
            $category->groupBox = $command->getAssociatedGroupIds();
        }

        if ($command->getAssociatedShopIds()) {
            $this->addShopAssociation($command->getAssociatedShopIds());
        }

        if (false === $category->validateFields(false)) {
            throw new CategoryException('Invalid data for root category creation');
        }

        if (false === $category->save()) {
            throw new CannotAddCategoryException('Failed to create root category');
        }

        return $category;
    }
}
