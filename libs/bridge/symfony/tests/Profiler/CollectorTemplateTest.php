<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Profiler;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Source;
use Twig\TwigFilter;
use Twig\TwigFunction;
use TypeLang\Mapper\Bridge\Symfony\Tests\TestCase;

/**
 * The web profiler is not installed: the template is parsed with stubs of
 * the functions and filters of the web profiler and of the Twig bridge.
 */
final class CollectorTemplateTest extends TestCase
{
    public function testTemplateIsParsed(): void
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../templates', 'TypeLangMapper');

        $twig = new Environment($loader);
        $twig->addFunction(new TwigFunction('profiler_dump', static fn(): string => ''));

        foreach (['file_link', 'file_relative', 'abbr_class'] as $filter) {
            $twig->addFilter(new TwigFilter($filter, static fn(): string => ''));
        }

        $name = '@TypeLangMapper/Collector/mapper.html.twig';
        $source = $loader->getSourceContext($name);

        $module = $twig->parse($twig->tokenize(new Source($source->getCode(), $name, $source->getPath())));

        self::assertSame($name, $module->getTemplateName());
    }
}
