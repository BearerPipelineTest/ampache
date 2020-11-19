<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Label;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Model\Label;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\System\Core;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AddLabelAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'add_label';
    
    private UiInterface $ui;

    private ConfigContainerInterface $configContainer;

    public function __construct(
        UiInterface $ui,
        ConfigContainerInterface $configContainer
    ) {
        $this->ui              = $ui;
        $this->configContainer = $configContainer;
    }

    public function run(ServerRequestInterface $request): ?ResponseInterface
    {
        $this->ui->showHeader();
        
        // Must be at least a content manager or edit upload enabled
        if (
            !Access::check('interface', 50) &&
            $this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::UPLOAD_ALLOW_EDIT) === false
        ) {
            Ui::access_denied();

            $this->ui->showQueryStats();
            $this->ui->showFooter();
            
            return null;
        }

        if (!Core::form_verify('add_label', 'post')) {
            Ui::access_denied();

            $this->ui->showQueryStats();
            $this->ui->showFooter();

            return null;
        }

        // Remove unauthorized defined values from here
        if (filter_has_var(INPUT_POST, 'user')) {
            unset($_POST['user']);
        }
        if (filter_has_var(INPUT_POST, 'creation_date')) {
            unset($_POST['creation_date']);
        }

        $label_id = Label::create($_POST);
        if (!$label_id) {
            require_once Ui::find_template('show_add_label.inc.php');
        } else {
            show_confirmation(
                T_('No Problem'),
                T_('The Label has been added'),
                sprintf(
                    '%s/browse.php?action=label',
                    $this->configContainer->getWebPath()
                )
            );
        }
        
        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}