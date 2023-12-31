<?php

declare(strict_types=1);

namespace Conia\Route;

trait AddsBeforeAfter
{
    /** @psalm-var list<Before> */
    protected array $beforeHandlers = [];

    /** @psalm-var list<After> */
    protected array $afterHandlers = [];

    public function before(Before $beforeHandler): static
    {
        $this->beforeHandlers[] = $beforeHandler;

        return $this;
    }

    /** @return list<Before> */
    public function beforeHandlers(): array
    {
        return $this->beforeHandlers;
    }

    /** @psalm-param list<Before> $beforeHandlers */
    public function replaceBeforeHandlers(array $beforeHandlers): static
    {
        $this->beforeHandlers = $beforeHandlers;

        return $this;
    }

    public function after(After $afterHandler): static
    {
        $this->afterHandlers[] = $afterHandler;

        return $this;
    }

    /** @return list<After> */
    public function afterHandlers(): array
    {
        return $this->afterHandlers;
    }

    /** @psalm-param list<After> $afterHandlers */
    public function replaceAfterHandlers(array $afterHandlers): static
    {
        $this->afterHandlers = $afterHandlers;

        return $this;
    }
}
