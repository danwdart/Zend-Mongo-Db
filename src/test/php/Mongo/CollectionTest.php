<?
/**
 * @category   MongoDB
 * @package    Mongo
 * @copyright  2010, Campaign and Digital Intelligence Ltd
 * @license    New BSD License
 * @author     Tim Langley
 */

class testCollection extends Mongo_Collection					{
	const		COLLECTION_TEST			= 'Test';	
	protected 	$_strDatabase 			= Mongo_CollectionTest::TEST_DATABASE;
	protected 	$_strCollection			= testCollection::COLLECTION_TEST;
}
class testCollection_Document extends Mongo_Document			{
	
}

class Mongo_CollectionTest extends PHPUnit_Framework_TestCase	{	
	const	TEST_DATABASE	= "testMongo";
	
	private $_connMongo		= null;
	private $_dbMongo		= null;
	
	public function setUp()										{
		//Before we do anything we should drop any pre-existing test databases
		$this->_connMongo	= new Mongo_Connection();
		$this->_dbMongo		= new Mongo_Db(self::TEST_DATABASE, $this->_connMongo);
		$arrCollections		= $this->_dbMongo->getCollections();
		foreach($arrCollections AS $mongoCollection)
			$mongoCollection->drop();
	}
	//__construct
	public function testFAIL_construct_wrongVal()				{
		try 													{
			$colTest		= new testCollection("FAILURE_ITS_A_STRING");
			$this->fail("Exception expected");
		} catch (Mongo_Exception $e)							{
			$this->assertEquals(Mongo_Exception::ERROR_MISSING_VALUES, $e->getMessage());
		}
	}
	public function testFAIL_construct_wrongObject()			{
		try 													{
			$object			= (object)"Tim";
			$colTest		= new testCollection($object);
			$this->fail("Exception expected");
		} catch (Mongo_Exception $e)							{
			$this->assertEquals(Mongo_Exception::ERROR_MISSING_VALUES, $e->getMessage());
		}
	}
	public function testSUCCEED_construct_null()				{
		$colTest			= new testCollection();
		$this->assertEquals("testCollection", 					get_class($colTest));
		$this->assertEquals(testCollection::COLLECTION_TEST, 	$colTest->getCollectionName());
		$this->assertEquals(Mongo_CollectionTest::TEST_DATABASE,$colTest->getDatabaseName());
	}
	public function testSUCCEED_construct_Connection()			{
		$colTest			= new testCollection($this->_connMongo);
		$this->assertEquals("testCollection", 					get_class($colTest));
		$this->assertEquals(testCollection::COLLECTION_TEST, 	$colTest->getCollectionName());
		$this->assertEquals(Mongo_CollectionTest::TEST_DATABASE,$colTest->getDatabaseName());
	}
	public function testSUCCEED_construct_Database()			{
		$colTest			= new testCollection($this->_dbMongo);
		$this->assertEquals("testCollection", 					get_class($colTest));
		$this->assertEquals(testCollection::COLLECTION_TEST, 	$colTest->getCollectionName());
		$this->assertEquals($this->_dbMongo->__toString(),$colTest->getDatabaseName());
	}
	//__toString()
	public function testSUCCEED_toString()						{
		$colTest			= new testCollection();
		$this->assertEquals("testCollection", 					get_class($colTest));
		$strFullName		= sprintf("%s.%s", self::TEST_DATABASE, testCollection::COLLECTION_TEST);
		$this->assertEquals($strFullName, 						$colTest->__toString());
		$this->assertEquals($strFullName, 						(string)$colTest);
	}
	//createDocument
	public function testSUCCEED_create_MongoDocument()			{
		$colTest			= new testCollection();
		$this->assertEquals("testCollection", 					get_class($colTest));
		$mongoDocument		= $colTest->createDocument();
		$this->assertEquals("Mongo_Document",					get_class($mongoDocument));
	}
	public function testSUCCEED_create_ChildDocument()			{
		$colTest			= new testCollection();
		$this->assertEquals("testCollection", 					get_class($colTest));
		$colTest->setDefaultDocumentType("testCollection_Document");
		$mongoDocument		= $colTest->createDocument();
		$this->assertEquals("testCollection_Document",			get_class($mongoDocument));
	}
	//decodeReference

	//drop
	public function testSUCCEED_drop()							{
		$intNoCollections	= $this->_dbMongo->getCollections();
		$this->assertEquals(0, count($intNoCollections));
		
		$strTestCollection	= "testCollection";
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$intNoCollections	= $this->_dbMongo->getCollections();
		$this->assertEquals(0, count($intNoCollections));
		
		$mongoDocument		= new Mongo_Document(null, $collTest);
		$mongoDocument->key	= "value";
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["key"]);
		//Note: Creating a collection is a Lazy exercise (ie line 43 showed there were NO collections)
		$intNoCollections	= $this->_dbMongo->getCollections();
		$this->assertEquals(1, count($intNoCollections));
		
		$return 			= $collTest->drop();
		
		$intNoCollections	= $this->_dbMongo->getCollections();
		$this->assertEquals(0, count($intNoCollections));
	}
	//find
	public function testSUCCEED_findAll_One()					{
		$strTestCollection	= "testCollection";
		$arrDocument		= array("key" => "value");
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$mongoDocument		= new Mongo_Document($arrDocument, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["key"]);
		
		$cursor				= $collTest->find();
		$this->assertEquals(1,					count($cursor));
	}
	public function testSUCCEED_findAll_Two()					{
		$strTestCollection	= "testCollection";
		$arrDocument		= array("key"  => "value");
		$arrDocument2		= array("key2" => "value2");
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$mongoDocument		= new Mongo_Document($arrDocument, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["key"]);
		
		$mongoDocument		= new Mongo_Document($arrDocument2, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value2", 	$arrReturn["key2"]);
		
		$cursor				= $collTest->find();
		$this->assertEquals(2,			count($cursor));
	}
	public function testSUCCEED_findAll_filtered()				{
		$strTestCollection	= "testCollection";
		$arrDocument		= array("key"  => "value");
		$arrDocument2		= array("key2" => "value2");
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$mongoDocument		= new Mongo_Document($arrDocument, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["key"]);
		
		$mongoDocument		= new Mongo_Document($arrDocument2, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value2", 	$arrReturn["key2"]);
		
		$cursor				= $collTest->find();
		$this->assertEquals(2,			count($cursor));
		
		$cursor				= $collTest->find(array("key" => "value"));
		$this->assertEquals(1,			count($cursor));
	}
	//findOne
	public function testSUCCEED_findOne()						{
		$strTestCollection	= "testCollection";
		$arrDocument		= array (	"string" 	=> "value"
									,	"integer"	=> 1
									,	"array"		=> array("array_string" => "array_value")
									);
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$mongoDocument		= new Mongo_Document($arrDocument, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["string"]);
		
		$docMongo			= $collTest->findOne();
		$this->assertEquals("value", 			$docMongo->string);
		$this->assertEquals($arrReturn["_id"], 	$docMongo->_id);
	}
	//insert
	public function testSUCCEED_insert()						{
		$strTestCollection	= "testCollection";
		$arrDocument		= array("key" => "value");
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$mongoDocument		= new Mongo_Document($arrDocument, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["key"]);
	}
	public function testFAIL_insert_twice()						{
		$strTestCollection	= "testCollection";
		$arrDocument		= array("key" => "value");
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		
		$mongoDocument		= new Mongo_Document($arrDocument, $collTest);
		$arrReturn			= $collTest->insert($mongoDocument);
		$this->assertEquals("value", 	$arrReturn["key"]);
		
		try 													{
			$collTest->insert($mongoDocument);
			$this->fail("Exception expected");
		} catch (MongoCursorException $e) {
			$this->assertEquals("E11000 duplicate key error index: testMongo.testCollection.\$_id_  dup key: { : ObjectId('".$arrReturn["_id"]."') }", $e->getMessage());
		}
	}
	//count
	public function testSUCCEED_count()							{
		$strTestCollection	= "testCollection";
		$collTest			= $this->_dbMongo->getCollection($strTestCollection);
		$this->assertEquals($strTestCollection, $collTest->getCollectionName());
		$this->assertEquals(1, count($collTest));
	}
	
}