<?php

/**
 * This file is part of Gitonomy.
 *
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 * (c) Julien DIDIER <genzo.wm@gmail.com>
 *
 * This source file is subject to the GPL license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gitonomy\Git;

/**
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Log implements \Countable, \IteratorAggregate
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $revisions;

    /**
     * @var integer
     */
    protected $offset;

    /**
     * @var integer
     */
    protected $limit;

    public function __construct(Repository $repository, $revisions, $offset = null, $limit = null)
    {
        $this->repository = $repository;
        $this->revisions  = $revisions;
        $this->offset     = $offset;
        $this->limit      = $limit;
    }

    /**
     * @return string
     */
    public function getRevisions()
    {
        return $this->revisions;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return array
     */
    public function getCommits()
    {
        $offset    = null !== $this->offset ? '--skip='.((int) $this->offset) : '';
        $limit     = null !== $this->limit ? '-n '.((int) $this->limit) : '';
        $revisions = null !== $this->revisions ? $this->revisions : '--all';

        $args = array('--format=format:%H');

        if (null !== $this->offset) {
            $args[] = '--skip='.((int) $this->offset);
        }

        if (null !== $this->limit) {
            $args[] = '-n';
            $args[] = (int) $this->limit;
        }

        $args[] = null === $this->revisions ? '--all' : $this->revisions;

        $exp = explode("\n", $this->repository->run('log', $args));

        $result = array();
        foreach ($exp as $hash) {
            if ($hash == '') {
                continue;
            }
            $result[] = $this->repository->getCommit($hash);
        }

        return $result;
    }

    /**
     * @see Countable
     */
    public function count()
    {
        return $this->countCommits();
    }

    /**
     * @see IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getCommits());
    }

    /**
     * Count commits, without offset or limit.
     *
     * @return int
     */
    public function countCommits()
    {
        if (null === $this->revisions) {
            $output = $this->repository->run('rev-list', array('--all'));
        } else {
            $output = $this->repository->run('rev-list', array($this->revisions));
        }

        return count(explode("\n", $output)) - 1;
    }
}
