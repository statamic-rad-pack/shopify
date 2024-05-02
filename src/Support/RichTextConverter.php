<?php

namespace StatamicRadPack\Shopify\Support;

use Statamic\Fieldtypes\Bard\Augmentor;

class RichTextConverter
{
    public function convert($schema, $toBard = false)
    {
        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }

        if (($schema['type'] ?? '') == 'root' && count($schema['children']) > 0) {
            $html = $this->convert($schema['children']);
        } else {
            $html = '';

            foreach ($schema as $el) {
                switch ($el['type']) {
                    case 'paragraph':
                        $html .= $this->buildParagraph($el);
                        break;
                    case 'heading':
                        $html .= $this->buildHeading($el);
                        break;
                    case 'list':
                        $html .= $this->buildList($el);
                        break;
                    case 'list-item':
                        $html .= $this->buildListItem($el);
                        break;
                    case 'link':
                        $html .= $this->buildLink($el);
                        break;
                    case 'text':
                        $html .= $this->buildText($el);
                        break;
                }
            }

        }

        if ($toBard) {
            return (new Augmentor($this))->renderHtmlToProsemirror($html)['content'] ?? [];
        }

        return $html;
    }

    private function buildParagraph(array $el): string
    {
        return '<p>'.$this->convert($el['children'] ?? []).'</p>';
    }

    private function buildHeading(array $el): string
    {
        $level = $el['level'] ?? 6;

        return "<h{$level}>".$this->convert($el['children'] ?? [])."</h{$level}>";
    }

    private function buildList(array $el): string
    {
        if ($el['children'] ?? false) {
            if ($el['listType'] == 'ordered') {
                return '<ol>'.$this->convert($el['children'] ?? []).'</ol>';
            }

            return '<ul>'.$this->convert($el['children'] ?? []).'</ul>';
        }

        return '';
    }

    private function buildListItem(array $el): string
    {
        return '<li>'.$this->convert($el['children'] ?? []).'</li>';
    }

    private function buildLink(array $el): string
    {
        return '<a href="'.($el['url'] ?? '').'" title="'.($el['title'] ?? '').'" target="'.($el['target'] ?? '').'">'.$this->convert($el['children'] ?? []).'</a>';
    }

    private function buildText(array $el): string
    {
        if ($el['bold'] ?? false) {
            return '<strong>'.$el['value'].'</strong>';
        }

        if ($el['italic'] ?? false) {
            return '<em>'.$el['value'].'</em>';
        }

        return $el['value'];
    }
}
