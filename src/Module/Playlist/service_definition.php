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
 *
 */

declare(strict_types=1);

namespace Ampache\Module\Playlist;

use function DI\autowire;

return [
    PlaylistExporterInterface::class => autowire(PlaylistExporter::class),
    PlaylistLoaderInterface::class => autowire(PlaylistLoader::class),
    SearchType\SearchTypeMapperInterface::class => autowire(SearchType\SearchTypeMapper::class),
    SearchType\AlbumSearchType::class => autowire(),
    SearchType\ArtistSearchType::class => autowire(),
    SearchType\LabelSearchType::class => autowire(),
    SearchType\PlaylistSearchType::class => autowire(),
    SearchType\SongSearchType::class => autowire(),
    SearchType\TagSearchType::class => autowire(),
    SearchType\UserSearchType::class => autowire(),
    SearchType\VideoSearchType::class => autowire(),
    PlaylistSongSorterInterface::class => autowire(PlaylistSongSorter::class),
];
