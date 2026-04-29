<?php

namespace Reno\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Reno\Cms\Interfaces\Contexts\ContextResolverInterface;
use Symfony\Component\HttpFoundation\Response;

class ContextMiddleware
{
    public function __construct(
        private readonly ContextResolverInterface $contextResolver,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $contextContainer = $this->contextResolver->resolve($request);

        $contextContainer->getContext()->modifyRequest($request);

        $request->attributes->set('cms_context', $contextContainer);
        $request->attributes->set('cms_context_id', $contextContainer->getId());

        $request->merge([
            'context_id' => $contextContainer->getId(),
        ]);

        app()->instance('cms.current_context', $contextContainer);
        app()->instance('cms.current_context_id', $contextContainer->getId());

        $locale = $contextContainer->getContext()->getLocale();
        app()->setLocale($locale);

        return $next($request);
    }
}
