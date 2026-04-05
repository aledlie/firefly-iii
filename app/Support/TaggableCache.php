<?php

/**
 * TaggableCache.php
 * Copyright (c) 2026 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
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

namespace FireflyIII\Support;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class TaggableCache
{
    public static function supportsTags(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    public static function tagged(array $tags): Repository
    {
        if (self::supportsTags()) {
            return Cache::tags($tags);
        }

        return Cache::store();
    }

    public static function flushTags(array $tags): void
    {
        if (self::supportsTags()) {
            Cache::tags($tags)->flush();
        }
        // For non-tag drivers, do nothing (individual keys expire or are forgotten manually)
    }
}
