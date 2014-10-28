<?PHP
namespace x3tech\LaravelShipper\Builder;

use x3tech\LaravelShipper\Builder\BuildStep\FigBuildStepInterface;

class FigBuilder
{
    /**
     * @var array[int]array[]BuildStepInterface
     */
    protected $steps;

    public function __construct()
    {
        $this->steps = array();
    }

    public function build()
    {
        $structure = array();

        foreach ($this->getPriorities() as $priority) {
            foreach ($this->steps[$priority] as $step) {
                $structure = $step->run($structure);
            }
        }

        return $structure;
    }

    protected function getPriorities()
    {
        $priorities = array_keys($this->steps);
        sort($priorities, SORT_NUMERIC);

        return $priorities;
    }

    public function addBuildStep(FigBuildStepInterface $step, $priority = 100)
    {
        $this->ensureArray($priority);
        $this->steps[$priority][] = $step;
    }

    private function ensureArray($priority)
    {
        if (!isset($this->steps[$priority])) {
            $this->steps[$priority] = array();
        }
    }
}
