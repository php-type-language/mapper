<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Node of a graph with two children, each of them refers back to the parent.
 */
final class BranchNodeStub
{
    public ?BranchNodeStub $parent = null;

    public ?BranchNodeStub $left = null;

    public ?BranchNodeStub $right = null;

    public static function create(): self
    {
        $root = new self();
        $root->left = new self();
        $root->right = new self();
        $root->left->parent = $root;
        $root->right->parent = $root;

        return $root;
    }
}
