<?php

namespace App\Policies;

use App\Models\HelpArticle;
use App\Models\Master\SysUser;

class HelpArticlePolicy
{
    public function viewAny(SysUser $sysUser): bool
    {
        return $sysUser->can('help.view');
    }

    public function view(SysUser $sysUser, HelpArticle $helpArticle): bool
    {
        return $sysUser->can('help.view');
    }

    public function create(SysUser $sysUser): bool
    {
        return $sysUser->can('help.create');
    }

    public function update(SysUser $sysUser, HelpArticle $helpArticle): bool
    {
        return $sysUser->can('help.edit');
    }

    public function delete(SysUser $sysUser, HelpArticle $helpArticle): bool
    {
        return $sysUser->can('help.delete');
    }

    public function restore(SysUser $sysUser, HelpArticle $helpArticle): bool
    {
        return $sysUser->can('help.edit');
    }

    public function forceDelete(SysUser $sysUser, HelpArticle $helpArticle): bool
    {
        return false;
    }

    public function publish(SysUser $sysUser, HelpArticle $helpArticle): bool
    {
        return $sysUser->can('help.publish');
    }
}
