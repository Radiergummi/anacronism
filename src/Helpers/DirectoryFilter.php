<?php
namespace Radiergummi\Anacronism\Helpers;

/**
 * DirectoryFilter class.
 * Taken from here: 
 * http://stackoverflow.com/a/15370487/2532203
 * 
 * @extends \RecursiveFilterIterator
 */
class DirectoryFilter extends \RecursiveFilterIterator
{
    /**
     * exclude
     * 
     * (default value: array())
     * 
     * @var array
     * @access protected
     */
    protected $exclude = [];


    /**
     * __construct function.
     * 
     * @access public
     * @param mixed $iterator
     * @param array $exclude
     */
    public function __construct(\Iterator $iterator, array $exclude)
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }


    /**
     * accept function.
     * 
     * @access public
     * @return bool
     */
    public function accept(): bool
    {
        return ! ($this->isDir() && in_array($this->getFilename(), $this->exclude));
    }


    /**
     * getChildren function.
     * 
     * @access public
     * @return DirectoryFilter
     */
    public function getChildren(): DirectoryFilter
    {
        return new DirectoryFilter($this->getInnerIterator()->getChildren(), $this->exclude);
    }
}
