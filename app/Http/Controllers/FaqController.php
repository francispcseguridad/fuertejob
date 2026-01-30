<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaqItem;

class FaqController extends Controller
{
    /**
     * Devuelve las preguntas frecuentes para trabajadores en formato JSON.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkerFaqs()
    {
        $faqs = FaqItem::where('target_audience', FaqItem::AUDIENCE_WORKER) // 'worker'
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'question', 'answer']);

        return response()->json($faqs);
    }

    /**
     * Devuelve las preguntas frecuentes para empresas en formato JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyFaqs()
    {
        $faqs = FaqItem::where('target_audience', FaqItem::AUDIENCE_COMPANY)
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'question', 'answer']);

        return response()->json($faqs);
    }
}
