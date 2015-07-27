<?
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
    protected $exclude = array();


    /**
     * __construct function.
     * 
     * @access public
     * @param mixed $iterator
     * @param array $exclude
     * @return void
     */
    public function __construct($iterator, array $exclude)
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }


    /**
     * accept function.
     * 
     * @access public
     * @return void
     */
    public function accept()
    {
        return ! ($this->isDir() && in_array($this->getFilename(), $this->exclude));
    }


    /**
     * getChildren function.
     * 
     * @access public
     * @return void
     */
    public function getChildren()
    {
        return new DirectoryFilter($this->getInnerIterator()->getChildren(), $this->exclude);
    }
}
