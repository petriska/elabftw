<?php
/**
 * \Elabftw\Elabftw\Links
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use Exception;

/**
 * All about the experiments links
 */
class Links implements CrudInterface
{
    /** @var Db $Db SQL Database */
    protected $Db;

    /** @var Experiments $Entity instance of Experiments */
    public $Entity;

    /**
     * Constructor
     *
     * @param Experiments $entity
     */
    public function __construct(Experiments $entity)
    {
        $this->Db = Db::getConnection();
        $this->Entity = $entity;
    }

    /**
     * Add a link to an experiment
     *
     * @param int $link ID of database item
     * @throws Exception
     * @return bool
     */
    public function create($link)
    {
        $sql = "INSERT INTO experiments_links (item_id, link_id) VALUES(:item_id, :link_id)";
        $req = $this->Db->prepare($sql);
        $req->bindParam(':item_id', $this->Entity->id);
        $req->bindParam(':link_id', $link);

        return $req->execute();
    }

    /**
     * Get links for an experiments
     *
     * @return array|false
     */
    public function readAll()
    {
        $sql = "SELECT items.id AS itemid,
            experiments_links.id AS linkid,
            experiments_links.*,
            items.*,
            items_types.*
            FROM experiments_links
            LEFT JOIN items ON (experiments_links.link_id = items.id)
            LEFT JOIN items_types ON (items.type = items_types.id)
            WHERE experiments_links.item_id = :id";
        $req = $this->Db->prepare($sql);
        $req->bindParam(':id', $this->Entity->id);
        $req->execute();
        if ($req->rowCount() > 0) {
            return $req->fetchAll();
        }

        return false;
    }

    /**
     * Copy the links from one experiment to an other.
     *
     * @param int $id The id of the original experiment
     * @param int $newId The id of the new experiment that will receive the links
     * @return null
     */
    public function duplicate($id, $newId)
    {
        // LINKS
        $linksql = "SELECT link_id FROM experiments_links WHERE item_id = :id";
        $linkreq = $this->Db->prepare($linksql);
        $linkreq->bindParam(':id', $id);
        $linkreq->execute();

        while ($links = $linkreq->fetch()) {
            $sql = "INSERT INTO experiments_links (link_id, item_id) VALUES(:link_id, :item_id)";
            $req = $this->Db->prepare($sql);
            $req->execute(array(
                'link_id' => $links['link_id'],
                'item_id' => $newId
            ));
        }
    }

    /**
     * Delete a link
     *
     * @param int $id ID of our link
     * @return bool
     */
    public function destroy($id)
    {
        $sql = "DELETE FROM experiments_links WHERE id= :id";
        $req = $this->Db->prepare($sql);
        $req->bindParam(':id', $id);

        return $req->execute();
    }

    /**
     * Delete all the links for an experiment
     *
     * @return bool
     */
    public function destroyAll()
    {
        $sql = "DELETE FROM experiments_links WHERE item_id = :item_id";
        $req = $this->Db->prepare($sql);
        $req->bindParam(':item_id', $this->Entity->id);

        return $req->execute();
    }
}
