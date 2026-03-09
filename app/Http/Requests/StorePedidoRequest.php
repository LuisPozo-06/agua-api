<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'cantidad_agua' => 'required|integer|min:1',
            'direccion_entrega' => 'required|string|max:255',
            'prioridad' => 'required|integer|min:1|max:5'
        ];
    }

    public function messages()
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente no existe',

            'cantidad_agua.required' => 'Debe indicar la cantidad de agua',
            'cantidad_agua.integer' => 'La cantidad debe ser un número',
            'cantidad_agua.min' => 'La cantidad mínima es 1000 litros',

            'direccion_entrega.required' => 'La dirección es obligatoria',

            'prioridad.required' => 'Debe indicar la prioridad',
            'prioridad.max' => 'La prioridad solo puede ser 1, 2 o 3'
        ];
    }
}
