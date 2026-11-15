<?php
/*
 * Mini template engine - shared by the PHP core and the WordPress theme.
 * Syntax (see references/cms-schema.md):
 *   {{ key }}  {{ key|br }}  {{#if key}}..{{else}}..{{/if}}  {{#each collection}}..{{/each}}
 * Everything is HTML-escaped by default. Output is never re-parsed (no template injection).
 */
if (!defined('SB')) { exit; }

final class SB_Engine
{
    private $schema;
    private $getSetting;   // callable(string $key): string
    private $getItems;     // callable(string $collection): array
    private $builtin;      // name => string|Closure (escaped on output)
    private $raw;          // name => string|Closure (trusted HTML, not escaped)
    private $base;         // path prefix for relative asset values
    private $types = array();
    private $collTypes = array();
    private $collKeys = array();
    private $lastClose = 'eof';

    public function __construct(array $schema, $getSetting, $getItems, $base, array $builtin = array(), array $raw = array())
    {
        $this->schema = $schema;
        $this->getSetting = $getSetting;
        $this->getItems = $getItems;
        $this->base = rtrim((string)$base, '/');
        $this->builtin = $builtin;
        $this->raw = $raw;
        foreach ((isset($schema['settings']) ? $schema['settings'] : array()) as $s) {
            $this->types[$s['key']] = isset($s['type']) ? $s['type'] : 'text';
        }
        foreach ((isset($schema['collections']) ? $schema['collections'] : array()) as $c) {
            $this->collKeys[$c['key']] = true;
            foreach ($c['fields'] as $f) {
                $this->collTypes[$c['key']][$f['key']] = isset($f['type']) ? $f['type'] : 'text';
            }
        }
    }

    public function render($tpl)
    {
        $parts = preg_split('/(\{\{[^{}]*\}\})/', (string)$tpl, -1, PREG_SPLIT_DELIM_CAPTURE);
        $i = 0;
        $nodes = $this->parseNodes($parts, $i);
        return $this->renderNodes($nodes, array());
    }

    private function parseNodes(array $parts, &$i)
    {
        $nodes = array();
        $n = count($parts);
        while ($i < $n) {
            $p = $parts[$i++];
            if ($p === '') { continue; }
            if (substr($p, 0, 2) !== '{{') { $nodes[] = array('text', $p); continue; }
            $tag = trim(substr($p, 2, -2));
            if (strpos($tag, '#each ') === 0) {
                $name = trim(substr($tag, 6));
                $body = $this->parseNodes($parts, $i);
                $nodes[] = array('each', $name, $body);
            } elseif (strpos($tag, '#if ') === 0) {
                $name = trim(substr($tag, 4));
                $then = $this->parseNodes($parts, $i);
                $else = array();
                if ($this->lastClose === 'else') { $else = $this->parseNodes($parts, $i); }
                $nodes[] = array('if', $name, $then, $else);
            } elseif ($tag === '/each' || $tag === '/if') {
                $this->lastClose = 'close';
                return $nodes;
            } elseif ($tag === 'else') {
                $this->lastClose = 'else';
                return $nodes;
            } else {
                $nodes[] = array('var', $tag);
            }
        }
        $this->lastClose = 'eof';
        return $nodes;
    }

    private function renderNodes(array $nodes, array $ctx)
    {
        $o = '';
        foreach ($nodes as $nd) {
            if ($nd[0] === 'text') {
                $o .= $nd[1];
            } elseif ($nd[0] === 'var') {
                $o .= $this->renderVar($nd[1], $ctx);
            } elseif ($nd[0] === 'if') {
                $o .= $this->renderNodes($this->truthy($nd[1], $ctx) ? $nd[2] : $nd[3], $ctx);
            } elseif ($nd[0] === 'each') {
                $items = call_user_func($this->getItems, $nd[1]);
                $count = count($items);
                $k = 0;
                foreach ($items as $item) {
                    $k++;
                    $c = $item;
                    $c['@index'] = (string)$k;
                    $c['@first'] = ($k === 1) ? '1' : '';
                    $c['@last'] = ($k === $count) ? '1' : '';
                    $c['__coll'] = $nd[1];
                    $o .= $this->renderNodes($nd[2], $c);
                }
            }
        }
        return $o;
    }

    private function resolve($v)
    {
        return ($v instanceof Closure) ? (string)$v() : (string)$v;
    }

    private function lookup($key, array $ctx, &$type)
    {
        $type = 'text';
        if (array_key_exists($key, $ctx)) {
            if (isset($ctx['__coll'], $this->collTypes[$ctx['__coll']][$key])) {
                $type = $this->collTypes[$ctx['__coll']][$key];
            }
            return (string)$ctx[$key];
        }
        if (isset($this->types[$key])) {
            $type = $this->types[$key];
            return (string)call_user_func($this->getSetting, $key);
        }
        if (array_key_exists($key, $this->builtin)) {
            return $this->resolve($this->builtin[$key]);
        }
        return '';
    }

    private function truthy($key, array $ctx)
    {
        if (!array_key_exists($key, $ctx) && isset($this->collKeys[$key])) {
            $items = call_user_func($this->getItems, $key);
            return count($items) > 0;
        }
        $v = $this->lookup($key, $ctx, $type);
        return $v !== '' && $v !== '0';
    }

    private function safeUrl($u)
    {
        $u = trim($u);
        if ($u === '') { return ''; }
        if (preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $u)) { return $u; }
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $u)) { return ''; }   // javascript:, data:, etc.
        return $u;
    }

    private function assetUrl($u)
    {
        $u = trim($u);
        if ($u === '') { return ''; }
        if (preg_match('#^https?://#i', $u)) { return $u; }
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $u)) { return ''; }
        if ($u[0] === '/') { return $u; }
        return $this->base . '/' . $u;
    }

    private function renderVar($tag, array $ctx)
    {
        $filter = '';
        if (strpos($tag, '|') !== false) {
            $bits = explode('|', $tag, 2);
            $tag = $bits[0];
            $filter = trim($bits[1]);
        }
        $key = trim($tag);
        if ($key === '') { return ''; }
        if (array_key_exists($key, $this->raw)) {
            return $this->resolve($this->raw[$key]);
        }
        $type = 'text';
        $val = $this->lookup($key, $ctx, $type);
        if ($type === 'image') {
            $val = $this->assetUrl($val);
        } elseif ($type === 'url') {
            $val = $this->safeUrl($val);
        } elseif ($type === 'color') {
            $val = preg_match('/^#[0-9a-fA-F]{3,8}$/', $val) ? $val : '';
        }
        $esc = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($filter === 'br') { $esc = nl2br($esc, false); }
        return $esc;
    }
}
