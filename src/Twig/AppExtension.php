<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\CommentHelper;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private $commentHelper;
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(CommentHelper $commentHelper, CacheInterface $cache)
    {
        $this->commentHelper = $commentHelper;
        $this->cache = $cache;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('user_activity_text', [$this, 'getUserActivityText']),
        ];
    }

    public function getUserActivityText(User $user): string
    {
        $key = sprintf('user_activity_text' . $user->getId());
        return $this->cache->get($key, function (ItemInterface $item) use ($user) {
            $item->expiresAfter(3600);
            return $this->calculateUserActivity($user);
        });
    }

    private function calculateUserActivity(User $user): string
    {
        $commentCount = $this->commentHelper->countRecentCommentsForUser($user);

        if ($commentCount > 50) {
            return 'bigfoot fanatic';
        }

        if ($commentCount > 30) {
            return 'believer';
        }

        if ($commentCount > 20) {
            return 'hobbyist';
        }

        return 'skeptic';
    }
}
