<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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
 */

declare(strict_types=1);

namespace Ampache\Repository;

use Ampache\Module\System\Dba;

final class UserPreferenceRepository implements UserPreferenceRepositoryInterface
{
    /**
     * @return mixed
     */
    public function getByUserAndPreference(
        int $userId,
        int $preferenceId
    ) {
        $db_results = Dba::read(
            'SELECT `value` FROM `user_preference` WHERE `preference`= ? AND `user` = ?',
            [$preferenceId, $userId]
        );
        if (Dba::num_rows($db_results) < 1) {
            $db_results = Dba::read(
                "SELECT `value` FROM `user_preference` WHERE `preference` = ? AND `user`='-1'",
                [$preferenceId]
            );
        }
        $data = Dba::fetch_assoc($db_results);

        return $data['value'];
    }
}