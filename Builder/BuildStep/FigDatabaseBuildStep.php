<?php
namespace x3tech\LaravelShipper\Builder\BuildStep;

use Illuminate\Config\Repository;

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
    public function run(array $structure)
    {
        $conn = $this->getConnection();
        if (!array_key_exists($conn['driver'], self::$supported)) {
            return $structure;
        }

        $callback = array($this, self::$supported[$conn['driver']]);
        return call_user_func($callback, $structure, $conn);
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
     * @param array $structure
     * @param array $conn Database connection config
     *
     * @return array
     */
    protected function addMysql(array $structure, array $conn)
    {
        $structure['db'] = array(
            'image' => 'x3tech/mysql',
            'environment' => array(
                'MYSQL_ROOT_PASSWORD' => $conn['password'],
                'MYSQL_DATABASE' => $conn['database'],
                'MYSQL_USER' => $conn['username'],
                'MYSQL_PASSWORD' => $conn['password']
            )
        );
        $structure['app']['links'][] = 'db';

        return $structure;
    }

    /**
     * Add PostgreSQL container to the fig.yml structure
     *
     * @param array $structure
     * @param array $conn Database connection config
     *
     * @return array
     */
    protected function addPostgreSql(array $structure, array $conn)
    {
        $structure['db'] = array(
            'image' => 'orchardup/postgresql',
            'environment' => array(
                'POSTGRESQL_DB' => $conn['database'],
                'POSTGRESQL_USER' => $conn['username'],
                'POSTGRESQL_PASS' => $conn['password']
            )
        );
        $structure['app']['links'][] = 'db';

        return $structure;
    }
}
