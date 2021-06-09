<?php
/**
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

declare(strict_types=0);

namespace Ampache\Module\Api\Ajax;

use Ampache\Module\Api\ApplicationInterface;
use Ampache\Module\Api\Ajax\Handler\ActionInterface;
use Ampache\Module\System\Core;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Container\ContainerInterface;
use function debug_event;
use function xoutput_headers;

final class AjaxApplication implements ApplicationInterface
{
    private const HANDLER_LIST = [
        'browse' => [
            'browse' => Handler\Browse\BrowseAction::class,
            'set_sort' => Handler\Browse\SetSortAction::class,
            'toggle_tag' => Handler\Browse\ToggleTagAction::class,
            'delete_object' => Handler\Browse\DeleteObjectAction::class,
            'page' => Handler\Browse\PageAction::class,
            'show_art' => Handler\Browse\ShowArtAction::class,
            'get_filters' => Handler\Browse\GetFiltersAction::class,
            'options' => Handler\Browse\OptionsAction::class,
            'get_share_links' => Handler\Browse\GetShareLinksAction::class,
        ],
        'catalog' => [
            'flip_state' => Handler\Catalog\FlipStateAction::class,
        ],
        'default' => [
            'refresh_rightbar' => Handler\Defaults\RefreshRightbarAction::class,
            'current_playlist' => Handler\Defaults\CurrentPlaylistAction::class,
            'basket' => Handler\Defaults\BasketAction::class,
            'set_rating' => Handler\Defaults\SetRatingAction::class,
            'set_userflag' => Handler\Defaults\SetUserflagAction::class,
            'action_buttons' => Handler\Defaults\ActionButtonsAction::class,
        ],
        'democratic' => [
            'delete_vote' => Handler\DemocraticPlayback\DeleteVoteAction::class,
            'add_vote' => Handler\DemocraticPlayback\AddVoteAction::class,
            'delete' => Handler\DemocraticPlayback\DeleteAction::class,
            'send_playlist' => Handler\DemocraticPlayback\SendPlaylistAction::class,
            'clear_playlist' => Handler\DemocraticPlayback\ClearPlaylistAction::class,
        ],
        'index' => [
            'random_albums' => Handler\Index\RandomAlbumsAction::class,
            'random_videos' => Handler\Index\RandomVideosAction::class,
            'artist_info' => Handler\Index\ArtistInfoAction::class,
            'similar_artist' => Handler\Index\SimilarArtistAction::class,
            'similar_now_playing' => Handler\Index\SimilarNowPlayingAction::class,
            'labels' => Handler\Index\LabelsAction::class,
            'wanted_missing_albums' => Handler\Index\WantedMissingAlbumsAction::class,
            'add_wanted' => Handler\Index\AddWantedAction::class,
            'remove_wanted' => Handler\Index\RemoveWantedAction::class,
            'accept_wanted' => Handler\Index\AcceptWantedAction::class,
            'reloadnp' => Handler\Index\ReloadNpAction::class,
            'sidebar' => Handler\Index\SidebarAction::class,
            'start_channel' => Handler\Index\StartChannelAction::class,
            'stop_channel' => Handler\Index\StopChannelAction::class,
            'slideshow' => Handler\Index\SlideshowAction::class,
            'songs' => Handler\Index\SongsAction::class,
        ],
        'localplay' => [
            'set_instance' => Handler\LocalPlay\SetInstanceAction::class,
            'command' => Handler\LocalPlay\CommandAction::class,
            'delete_track' => Handler\LocalPlay\DeleteTrackAction::class,
            'delete_instance' => Handler\LocalPlay\DeleteInstanceAction::class,
            'repeat' => Handler\LocalPlay\RepeatAction::class,
            'random' => Handler\LocalPlay\RandomAction::class,
        ],
        'player' => [
            'show_broadcasts' => Handler\Player\ShowBroadcastsAction::class,
            'broadcast' => Handler\Player\BroadcastAction::class,
            'unbroadcast' => Handler\Player\UnbroadcastAction::class,
        ],
        'playlist' => [
            'delete_track' => Handler\Playlist\DeleteTrackAction::class,
            'append_item' => Handler\Playlist\AppendItemAction::class,
        ],
        'podcast' => [
            'sync' => Handler\Podcast\SyncAction::class,
        ],
        'random' => [
            'song' => Handler\Random\SongAction::class,
            'album' => Handler\Random\AlbumAction::class,
            'artist' => Handler\Random\ArtistAction::class,
            'playlist' => Handler\Random\PlaylistAction::class,
            'advanced_random' => Handler\Random\AdvancedRandomAction::class,
        ],
        'search' => [
            'search' => Handler\Search\SearchAction::class,
        ],
        'song' => [
            'fiip_state' => Handler\Song\FlipStateAction::class,
            'shouts' => Handler\Song\ShoutsAction::class,
        ],
        'stats' => [
            'geolocation' => Handler\Stats\GeolocationAction::class,
        ],
        'stream' => [
            'set_play_type' => Handler\Stream\SetPlayTypeAction::class,
            'directplay' => Handler\Stream\DirectplayAction::class,
            'basket' => Handler\Stream\BasketAction::class,
        ],
        'tag' => [
            'get_tag_map' => Handler\Tag\GetTagMapAction::class,
            'get_labels' => Handler\Tag\GetLabelsAction::class,
            'add_filter' => Handler\Tag\AddFilterAction::class,
            'browse_type' => Handler\Tag\BrowseTypeAction::class,
            'add_tag_by_name' => Handler\Tag\AddTageByNameAction::class,
            'delete' => Handler\Tag\DeleteAction::class,
            'add_tag' => Handler\Tag\AddTagAction::class,
            'remove_tag_map' => Handler\Tag\RemoveTagMap::class,
        ],
        'user' => [
            'flip_follow' => Handler\User\FlipFollowAction::class
        ],
    ];

    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;
    }

    public function run(): void
    {
        xoutput_headers();

        $request = $this->dic->get(ServerRequestCreatorInterface::class)->fromGlobals();

        $queryParams = $request->getQueryParams();

        $page = $queryParams['page'] ?? null;
        if ($page) {
            debug_event('ajax.server', 'Called for page: {' . $page . '}', 5);
        }

        $action = $queryParams['action'] ?? null;

        $handlerClassName = static::HANDLER_LIST[$page] ?? static::HANDLER_LIST['default'];

        if (array_key_exists($action, $handlerClassName)) {
            /** @var ActionInterface $handler */
            $handler = $this->dic->get($handlerClassName[$action]);

            $result = $handler->handle(
                $request,
                $this->dic->get(Psr17Factory::class)->createResponse(),
                Core::get_global('user')
            );
        } else {
            $result = ['rfc3514' => '0x1'];
        }

        // We always do this
        echo xoutput_from_array($result);
    }
}