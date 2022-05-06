<?php

namespace Hgabka\Doctrine\Translatable\Mapping\Driver;

use Doctrine\Bundle\DoctrineBundle\Mapping\MappingDriver as BundleMappingDriver;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver as DoctrineAttributeDriver;
use Doctrine\ORM\Mapping\Driver\FileDriver as DoctrineFileDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Metadata\Driver\DriverChain;
use Metadata\Driver\DriverInterface;

/**
 * Adapt a Doctrine metadata driver
 */
class DoctrineAdapter
{
    /**
     * Create a driver from a Doctrine registry
     *
     * @param ManagerRegistry $registry
     *
     * @return DriverInterface
     */
    public static function fromRegistry(ManagerRegistry $registry)
    {
        $drivers = [];
        foreach ($registry->getManagers() as $manager) {
            $drivers[] = self::fromManager($manager);
        }

        return new DriverChain($drivers);
    }

    /**
     * Create a driver from a Doctrine object manager
     *
     * @param ObjectManager $om
     *
     * @return DriverInterface
     */
    public static function fromManager(ObjectManager $om)
    {
        return self::fromMetadataDriver($om->getConfiguration()->getMetadataDriverImpl());
    }

    public static function fromMetadataDriver(BundleMappingDriver $omDriver)
    {
        return self::getFromMetadataDriver($omDriver->getDriver());
    }

    /**
     * Create a driver from a Doctrine metadata driver
     *
     * @param MappingDriver $omDriver
     *
     * @return DriverInterface
     */
    public static function getFromMetadataDriver(MappingDriver $omDriver)
    {
        if ($omDriver instanceof MappingDriverChain) {
            $drivers = [];
            foreach ($omDriver->getDrivers() as $nestedOmDriver) {
                $drivers[] = self::getFromMetadataDriver($nestedOmDriver);
            }

            return new DriverChain($drivers);
        }

        if ($omDriver instanceof DoctrineAnnotationDriver) {
            return new AnnotationDriver($omDriver->getReader());
        }

        if ($omDriver instanceof DoctrineAttributeDriver) {
            return new AttributeDriver($omDriver->getReader());
        }

        if ($omDriver instanceof DoctrineFileDriver) {
            $reflClass = new \ReflectionClass($omDriver);

            $driverName = $reflClass->getShortName();
            if ($omDriver instanceof SimplifiedYamlDriver || $omDriver instanceof SimplifiedXmlDriver) {
                $driverName = str_replace('Simplified', '', $driverName);
            }

            $class = 'Hgabka\\Doctrine\\Translatable\\Mapping\\Driver\\' . $driverName;

            if (class_exists($class)) {
                return new $class($omDriver->getLocator());
            }
        }

        throw new \InvalidArgumentException('Cannot adapt Doctrine driver of class ' . get_class($omDriver));
    }
}
