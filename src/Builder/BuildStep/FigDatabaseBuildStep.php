<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\Fig\Container;

/**
 * Add database container definition to fig.yml for supported database drivers
 *
 * @see FigBuildStepInterface
 */
class FigDatabaseBuildStep implements FigBuildStepInterface
{
    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    protected static $supported = array(
        'mysql' => 'addMysql',
        # 'pgsql' => 'addPostgreSql', // Disabled due to HHVM not supporting it
    );

    public function __construct(
        \Illuminate\Config\Repository $config
    ) {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Definition $definition)
    {
        $conn = $this->getConnection();
        if (!$this->isSupported($conn)) {
            return;
        }

        $db = $this->getDatabaseContainer($conn);

        $definition->addContainer($db);
        $definition->getContainer('app')->addLink($db);
    }

    /**
     * Get database container for the current driver
     *
     * @param Definition $definition
     * @param array $conn
     *
     * @return Container Database container
     */
    protected function getDatabaseContainer(array $conn)
    {
        $method = self::$supported[$conn['driver']];
        return $this->$method($conn);
    }

    /**
     * Returns whether the database driver is supported
     *
     * @param array $conn
     *
     * @return bool
     */
    protected function isSupported(array $conn)
    {
        return array_key_exists($conn['driver'], self::$supported);
    }

    /**
     * Get the configured default connection
     *
     * @return array
     */
    protected function getConnection()
    {
        $dbConfig = $this->config->get('database');
        return $dbConfig['connections'][$dbConfig['default']];
    }

    /**
     * Add MySQL container to the fig.yml structure
     *
     * @param Definition $definition
     * @param array $conn Database connection config
     *
     * @return Container
     */
    protected function addMysql(array $conn)
    {
        $db = new Container('db');
        $db->setImage('x3tech/mysql');
        $db->setEnvironment(array(
            'MYSQL_ROOT_PASSWORD' => $conn['password'],
            'MYSQL_DATABASE' => $conn['database'],
            'MYSQL_USER' => $conn['username'],
            'MYSQL_PASSWORD' => $conn['password']
        ));
        return $db;
    }

    /**
     * Add PostgreSQL container to the fig.yml structure
     *
     * @param Definition $definition
     * @param array $conn Database connection config
     *
     * @return Container
     */
    protected function getPostgreSql(array $conn)
    {
        $db = new Container('db');
        $db->setImage('orchardup/postgresql');
        $db->setEnvironment(array(
            'POSTGRESQL_DB' => $conn['database'],
            'POSTGRESQL_USER' => $conn['username'],
            'POSTGRESQL_PASS' => $conn['password']
        ));
        return $db;
    }
}
