<?php

namespace App\Module\Import\Service;

use App\Module\Elemento\Entity\Elemento;
use App\Module\Geo\Repository\EstadoRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    private $em;
    private $estadoRepository;

    public function __construct(EntityManagerInterface $em, EstadoRepository $estadoRepository)
    {
        $this->em = $em;
        $this->estadoRepository = $estadoRepository;
    }

    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) <= 1) {
            throw new \InvalidArgumentException('El archivo Excel está vacío o solo contiene la cabecera.');
        }

        $headers = array_shift($rows);
        $headers = array_map('strtolower', array_map('trim', $headers));

        $colNombre = array_search('nombre', $headers) ?: 'A';
        $colTipo = array_search('tipo', $headers) ?: 'B';
        $colLatitud = array_search('latitud', $headers) ?: 'C';
        $colLongitud = array_search('longitud', $headers) ?: 'D';
        $colDireccion = array_search('direccion', $headers) ?: 'E';
        $colActividad = array_search('actividad', $headers) ?: 'F';
        $colTecnologia = array_search('tecnologia', $headers) ?: 'G';
        $colCantidad = array_search('cantidad', $headers) ?: 'H';
        $colSegmentacion = array_search('segmentacion', $headers) ?: 'I';
        $colCodigoDealer = array_search('codigo_dealer', $headers) ?: 'J';
        $colClasificacion = array_search('clasificacion', $headers) ?: 'K';
        $colEstado = array_search('estado', $headers) ?: 'L';

        $importedCount = 0;
        $errors = [];
        $stateCache = [];

        foreach ($rows as $rowIndex => $row) {
            $nombre = $row[$colNombre] ?? null;
            $tipo = $row[$colTipo] ?? null;

            if (!$nombre || !$tipo) {
                $errors[] = "Fila $rowIndex omitida: Nombre o Tipo vacío.";
                continue;
            }

            $tipo = strtolower(trim($tipo));
            $validTipos = ['antenas', 'abonados', 'oficinas', 'agentes'];
            if (!in_array($tipo, $validTipos)) {
                $errors[] = "Fila $rowIndex omitida: El Tipo '$tipo' no es válido.";
                continue;
            }

            $elemento = new Elemento();
            $elemento->setNombre(trim($nombre));
            $elemento->setTipo($tipo);
            $elemento->setLatitud($row[$colLatitud] ?? null);
            $elemento->setLongitud($row[$colLongitud] ?? null);
            $elemento->setDireccion($row[$colDireccion] ?? null);
            $elemento->setActividad($row[$colActividad] ?? 'Operativa');
            $elemento->setTecnologia($row[$colTecnologia] ?? null);
            $elemento->setCantidad((int) ($row[$colCantidad] ?? 0));
            $elemento->setSegmentacion($row[$colSegmentacion] ?? null);
            $elemento->setCodigoDealer($row[$colCodigoDealer] ?? null);
            $elemento->setClasificacion($row[$colClasificacion] ?? null);

            $estadoNameInput = $row[$colEstado] ?? null;
            if ($estadoNameInput) {
                $estadoName = trim($estadoNameInput);
                if (!isset($stateCache[$estadoName])) {
                    $estado = $this->estadoRepository->findOneBy(['nombreEstado' => $estadoName]);
                    
                    if (!$estado) {
                        $qb = $this->estadoRepository->createQueryBuilder('e')
                            ->where('LOWER(e.nombreEstado) LIKE LOWER(:name)')
                            ->setParameter('name', '%' . $estadoName . '%')
                            ->setMaxResults(1);
                        $estado = $qb->getQuery()->getOneOrNullResult();
                    }
                    
                    $stateCache[$estadoName] = $estado;
                }

                if ($stateCache[$estadoName]) {
                    $elemento->setEstado($stateCache[$estadoName]);
                }
            }

            $this->em->persist($elemento);
            $importedCount++;

            if (($importedCount % 50) === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        return [
            'total_importados' => $importedCount,
            'detalles_omisiones' => $errors
        ];
    }
}
