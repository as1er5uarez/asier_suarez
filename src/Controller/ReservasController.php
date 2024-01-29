<?php


namespace App\Controller;

use App\Entity\Apartment;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/reservas")
 */
class ReservasController extends AbstractController
{
    /**
     * @Route("/", methods={"POST"})
     */
    public function crearReserva(Request $request): JsonResponse
    {
        // Obtener datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        // Validar campos obligatorios
        if (empty($data['apartment_id']) || empty($data['start_date']) || empty($data['end_date']) || empty($data['customer_email'])) {
            return new JsonResponse(['error' => 'Todos los campos son obligatorios'], 400);
        }

        // Verificar si el apartamento existe
        $apartment = $this->getDoctrine()->getRepository(Apartment::class)->find($data['apartment_id']);
        if (!$apartment) {
            return new JsonResponse(['error' => 'Apartamento no encontrado'], 404);
        }

        // Verificar disponibilidad del apartamento en las fechas especificadas
        $reservas = $this->getDoctrine()->getRepository(Reservation::class)->verificarDisponibilidad(
            $data['apartment_id'],
            $data['start_date'],
            $data['end_date']
        );

        if (!empty($reservas)) {
            return new JsonResponse(['error' => 'El apartamento ya está reservado para esas fechas'], 400);
        }

        // Crear la reserva
        $reserva = new Reservation();
        $reserva->setApartment($apartment);
        $reserva->setStartDate(new \DateTime($data['start_date']));
        $reserva->setEndDate(new \DateTime($data['end_date']));
        $reserva->setCreatedAt($data['customer_email']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reserva);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reserva creada con éxito'], 201);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function anularReserva(int $id, Request $request): JsonResponse
    {
        // Verificar si la reserva existe
        $reserva = $this->getDoctrine()->getRepository(Reservation::class)->find($id);
        if (!$reserva) {
            return new JsonResponse(['error' => 'Reserva no encontrada'], 404);
        }

        // Verificar si la reserva ya está anulada
        if ($reserva->getAnulada()) {
            return new JsonResponse(['error' => 'La reserva ya está anulada'], 400);
        }

        // Marcar la reserva como anulada y registrar la fecha de anulación
        $reserva->setAnulada(true);
        $reserva->setFechaAnulacion(new \DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($reserva);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reserva anulada con éxito'], 200);
    }
}
