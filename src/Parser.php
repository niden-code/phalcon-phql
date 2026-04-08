<?php

declare(strict_types=1);

namespace Phalcon\Phql;

use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Phql\Scanner\Scanner;
use Phalcon\Phql\Scanner\State;
use Phalcon\Phql\Scanner\Token;
use RuntimeException;
use stdClass;

/**
 * Orchestrates the PHQL lexer and parser, equivalent to
 * phql_internal_parse_phql() in base.c.
 */
class Parser
{
    private bool $enableLiterals;

    public function __construct(bool $enableLiterals = true)
    {
        $this->enableLiterals = $enableLiterals;
    }

    /**
     * Parse a PHQL string and return the AST array.
     *
     * @throws RuntimeException on syntax or scanner error
     */
    public function parse(string $phql): array
    {
        if ($phql === '') {
            throw new RuntimeException('PHQL statement cannot be NULL');
        }

        $state   = new State($phql);
        $token   = new Token();
        $scanner = new Scanner($state, $token);

        $parserObject = new \phql_Parser();

        // Status object mirrors phql_parser_status in C
        $status                  = new stdClass();
        $status->status          = \phql_Parser::PHQL_PARSING_OK;
        $status->scanner_state   = $state;
        $status->ret             = null;
        $status->syntax_error    = null;
        $status->token           = $token;
        $status->enable_literals = $this->enableLiterals;
        $status->phql            = $phql;
        $status->phql_length     = mb_strlen($phql);

        $parserObject->status = $status;

        $errorMsg = null;
        $failed   = false;

        while (($scannerStatus = $scanner->scanForToken()) >= 0) {
            // Equivalent to: state->start_length = (phql + phql_length - state->start)
            $state->startLength = $status->phql_length - $state->getCursor();

            $state->activeToken = $token->opcode;

            switch ($token->opcode) {
                case Opcode::PHQL_T_IGNORE:
                    break;

                case Opcode::PHQL_T_ADD:
                    $parserObject->phql_(\phql_Parser::PHQL_PLUS);
                    break;
                case Opcode::PHQL_T_SUB:
                    $parserObject->phql_(\phql_Parser::PHQL_MINUS);
                    break;
                case Opcode::PHQL_T_MUL:
                    $parserObject->phql_(\phql_Parser::PHQL_TIMES);
                    break;
                case Opcode::PHQL_T_DIV:
                    $parserObject->phql_(\phql_Parser::PHQL_DIVIDE);
                    break;
                case Opcode::PHQL_T_MOD:
                    $parserObject->phql_(\phql_Parser::PHQL_MOD);
                    break;
                case Opcode::PHQL_T_AND:
                    $parserObject->phql_(\phql_Parser::PHQL_AND);
                    break;
                case Opcode::PHQL_T_OR:
                    $parserObject->phql_(\phql_Parser::PHQL_OR);
                    break;
                case Opcode::PHQL_T_EQUALS:
                    $parserObject->phql_(\phql_Parser::PHQL_EQUALS);
                    break;
                case Opcode::PHQL_T_NOTEQUALS:
                    $parserObject->phql_(\phql_Parser::PHQL_NOTEQUALS);
                    break;
                case Opcode::PHQL_T_LESS:
                    $parserObject->phql_(\phql_Parser::PHQL_LESS);
                    break;
                case Opcode::PHQL_T_GREATER:
                    $parserObject->phql_(\phql_Parser::PHQL_GREATER);
                    break;
                case Opcode::PHQL_T_GREATEREQUAL:
                    $parserObject->phql_(\phql_Parser::PHQL_GREATEREQUAL);
                    break;
                case Opcode::PHQL_T_LESSEQUAL:
                    $parserObject->phql_(\phql_Parser::PHQL_LESSEQUAL);
                    break;

                case Opcode::PHQL_T_IDENTIFIER:
                    $parserObject->phql_(\phql_Parser::PHQL_IDENTIFIER, $this->makeParserToken($token));
                    break;

                case Opcode::PHQL_T_DOT:
                    $parserObject->phql_(\phql_Parser::PHQL_DOT);
                    break;
                case Opcode::PHQL_T_COMMA:
                    $parserObject->phql_(\phql_Parser::PHQL_COMMA);
                    break;

                case Opcode::PHQL_T_PARENTHESES_OPEN:
                    $parserObject->phql_(\phql_Parser::PHQL_PARENTHESES_OPEN);
                    break;
                case Opcode::PHQL_T_PARENTHESES_CLOSE:
                    $parserObject->phql_(\phql_Parser::PHQL_PARENTHESES_CLOSE);
                    break;

                case Opcode::PHQL_T_LIKE:
                    $parserObject->phql_(\phql_Parser::PHQL_LIKE);
                    break;
                case Opcode::PHQL_T_ILIKE:
                    $parserObject->phql_(\phql_Parser::PHQL_ILIKE);
                    break;
                case Opcode::PHQL_T_NOT:
                    $parserObject->phql_(\phql_Parser::PHQL_NOT);
                    break;
                case Opcode::PHQL_T_BITWISE_AND:
                    $parserObject->phql_(\phql_Parser::PHQL_BITWISE_AND);
                    break;
                case Opcode::PHQL_T_BITWISE_OR:
                    $parserObject->phql_(\phql_Parser::PHQL_BITWISE_OR);
                    break;
                case Opcode::PHQL_T_BITWISE_NOT:
                    $parserObject->phql_(\phql_Parser::PHQL_BITWISE_NOT);
                    break;
                case Opcode::PHQL_T_BITWISE_XOR:
                    $parserObject->phql_(\phql_Parser::PHQL_BITWISE_XOR);
                    break;
                case Opcode::PHQL_T_AGAINST:
                    $parserObject->phql_(\phql_Parser::PHQL_AGAINST);
                    break;
                case Opcode::PHQL_T_CASE:
                    $parserObject->phql_(\phql_Parser::PHQL_CASE);
                    break;
                case Opcode::PHQL_T_WHEN:
                    $parserObject->phql_(\phql_Parser::PHQL_WHEN);
                    break;
                case Opcode::PHQL_T_THEN:
                    $parserObject->phql_(\phql_Parser::PHQL_THEN);
                    break;
                case Opcode::PHQL_T_END:
                    $parserObject->phql_(\phql_Parser::PHQL_END);
                    break;
                case Opcode::PHQL_T_ELSE:
                    $parserObject->phql_(\phql_Parser::PHQL_ELSE);
                    break;
                case Opcode::PHQL_T_FOR:
                    $parserObject->phql_(\phql_Parser::PHQL_FOR);
                    break;
                case Opcode::PHQL_T_WITH:
                    $parserObject->phql_(\phql_Parser::PHQL_WITH);
                    break;

                case Opcode::PHQL_T_INTEGER:
                    if ($this->enableLiterals) {
                        $parserObject->phql_(\phql_Parser::PHQL_INTEGER, $this->makeParserToken($token));
                    } else {
                        $errorMsg       = 'Literals are disabled in PHQL statements';
                        $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    }
                    break;
                case Opcode::PHQL_T_DOUBLE:
                    if ($this->enableLiterals) {
                        $parserObject->phql_(\phql_Parser::PHQL_DOUBLE, $this->makeParserToken($token));
                    } else {
                        $errorMsg       = 'Literals are disabled in PHQL statements';
                        $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    }
                    break;
                case Opcode::PHQL_T_STRING:
                    if ($this->enableLiterals) {
                        $parserObject->phql_(\phql_Parser::PHQL_STRING, $this->makeParserToken($token));
                    } else {
                        $errorMsg       = 'Literals are disabled in PHQL statements';
                        $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    }
                    break;
                case Opcode::PHQL_T_TRUE:
                    if ($this->enableLiterals) {
                        $parserObject->phql_(\phql_Parser::PHQL_TRUE);
                    } else {
                        $errorMsg       = 'Literals are disabled in PHQL statements';
                        $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    }
                    break;
                case Opcode::PHQL_T_FALSE:
                    if ($this->enableLiterals) {
                        $parserObject->phql_(\phql_Parser::PHQL_FALSE);
                    } else {
                        $errorMsg       = 'Literals are disabled in PHQL statements';
                        $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    }
                    break;
                case Opcode::PHQL_T_HINTEGER:
                    if ($this->enableLiterals) {
                        $parserObject->phql_(\phql_Parser::PHQL_HINTEGER, $this->makeParserToken($token));
                    } else {
                        $errorMsg       = 'Literals are disabled in PHQL statements';
                        $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    }
                    break;

                case Opcode::PHQL_T_NPLACEHOLDER:
                    $parserObject->phql_(\phql_Parser::PHQL_NPLACEHOLDER, $this->makeParserToken($token));
                    break;
                case Opcode::PHQL_T_SPLACEHOLDER:
                    $parserObject->phql_(\phql_Parser::PHQL_SPLACEHOLDER, $this->makeParserToken($token));
                    break;
                case Opcode::PHQL_T_BPLACEHOLDER:
                    $parserObject->phql_(\phql_Parser::PHQL_BPLACEHOLDER, $this->makeParserToken($token));
                    break;

                case Opcode::PHQL_T_FROM:
                    $parserObject->phql_(\phql_Parser::PHQL_FROM);
                    break;
                case Opcode::PHQL_T_UPDATE:
                    $parserObject->phql_(\phql_Parser::PHQL_UPDATE);
                    break;
                case Opcode::PHQL_T_SET:
                    $parserObject->phql_(\phql_Parser::PHQL_SET);
                    break;
                case Opcode::PHQL_T_WHERE:
                    $parserObject->phql_(\phql_Parser::PHQL_WHERE);
                    break;
                case Opcode::PHQL_T_DELETE:
                    $parserObject->phql_(\phql_Parser::PHQL_DELETE);
                    break;
                case Opcode::PHQL_T_INSERT:
                    $parserObject->phql_(\phql_Parser::PHQL_INSERT);
                    break;
                case Opcode::PHQL_T_INTO:
                    $parserObject->phql_(\phql_Parser::PHQL_INTO);
                    break;
                case Opcode::PHQL_T_VALUES:
                    $parserObject->phql_(\phql_Parser::PHQL_VALUES);
                    break;
                case Opcode::PHQL_T_SELECT:
                    $parserObject->phql_(\phql_Parser::PHQL_SELECT);
                    break;
                case Opcode::PHQL_T_AS:
                    $parserObject->phql_(\phql_Parser::PHQL_AS);
                    break;
                case Opcode::PHQL_T_ORDER:
                    $parserObject->phql_(\phql_Parser::PHQL_ORDER);
                    break;
                case Opcode::PHQL_T_BY:
                    $parserObject->phql_(\phql_Parser::PHQL_BY);
                    break;
                case Opcode::PHQL_T_LIMIT:
                    $parserObject->phql_(\phql_Parser::PHQL_LIMIT);
                    break;
                case Opcode::PHQL_T_OFFSET:
                    $parserObject->phql_(\phql_Parser::PHQL_OFFSET);
                    break;
                case Opcode::PHQL_T_GROUP:
                    $parserObject->phql_(\phql_Parser::PHQL_GROUP);
                    break;
                case Opcode::PHQL_T_HAVING:
                    $parserObject->phql_(\phql_Parser::PHQL_HAVING);
                    break;
                case Opcode::PHQL_T_ASC:
                    $parserObject->phql_(\phql_Parser::PHQL_ASC);
                    break;
                case Opcode::PHQL_T_DESC:
                    $parserObject->phql_(\phql_Parser::PHQL_DESC);
                    break;
                case Opcode::PHQL_T_IN:
                    $parserObject->phql_(\phql_Parser::PHQL_IN);
                    break;
                case Opcode::PHQL_T_ON:
                    $parserObject->phql_(\phql_Parser::PHQL_ON);
                    break;
                case Opcode::PHQL_T_INNER:
                    $parserObject->phql_(\phql_Parser::PHQL_INNER);
                    break;
                case Opcode::PHQL_T_JOIN:
                    $parserObject->phql_(\phql_Parser::PHQL_JOIN);
                    break;
                case Opcode::PHQL_T_LEFT:
                    $parserObject->phql_(\phql_Parser::PHQL_LEFT);
                    break;
                case Opcode::PHQL_T_RIGHT:
                    $parserObject->phql_(\phql_Parser::PHQL_RIGHT);
                    break;
                case Opcode::PHQL_T_CROSS:
                    $parserObject->phql_(\phql_Parser::PHQL_CROSS);
                    break;
                case Opcode::PHQL_T_FULL:
                    $parserObject->phql_(\phql_Parser::PHQL_FULL);
                    break;
                case Opcode::PHQL_T_OUTER:
                    $parserObject->phql_(\phql_Parser::PHQL_OUTER);
                    break;
                case Opcode::PHQL_T_IS:
                    $parserObject->phql_(\phql_Parser::PHQL_IS);
                    break;
                case Opcode::PHQL_T_NULL:
                    $parserObject->phql_(\phql_Parser::PHQL_NULL);
                    break;
                case Opcode::PHQL_T_BETWEEN:
                    $parserObject->phql_(\phql_Parser::PHQL_BETWEEN);
                    break;
                case Opcode::PHQL_T_BETWEEN_NOT:
                    $parserObject->phql_(\phql_Parser::PHQL_BETWEEN_NOT);
                    break;
                case Opcode::PHQL_T_DISTINCT:
                    $parserObject->phql_(\phql_Parser::PHQL_DISTINCT);
                    break;
                case Opcode::PHQL_T_ALL:
                    $parserObject->phql_(\phql_Parser::PHQL_ALL);
                    break;
                case Opcode::PHQL_T_CAST:
                    $parserObject->phql_(\phql_Parser::PHQL_CAST);
                    break;
                case Opcode::PHQL_T_CONVERT:
                    $parserObject->phql_(\phql_Parser::PHQL_CONVERT);
                    break;
                case Opcode::PHQL_T_USING:
                    $parserObject->phql_(\phql_Parser::PHQL_USING);
                    break;
                case Opcode::PHQL_T_EXISTS:
                    $parserObject->phql_(\phql_Parser::PHQL_EXISTS);
                    break;

                default:
                    $status->status = \phql_Parser::PHQL_PARSING_FAILED;
                    $errorMsg       = sprintf('Scanner: Unknown opcode %d', $token->opcode);
                    break;
            }

            if ($status->status !== \phql_Parser::PHQL_PARSING_OK) {
                $failed = true;
                break;
            }
        }

        if (!$failed) {
            if (
                $scannerStatus === Scanner::PHQL_SCANNER_RETCODE_ERR
                || $scannerStatus === Scanner::PHQL_SCANNER_RETCODE_IMPOSSIBLE
            ) {
                if ($errorMsg === null) {
                    $errorMsg = $this->buildScannerErrorMsg($status, $phql);
                }
                $failed = true;
            } else {
                // Signal EOF to the parser
                $parserObject->phql_(0);
            }
        }

        $state->activeToken = 0;

        if ($status->status !== \phql_Parser::PHQL_PARSING_OK) {
            $failed = true;
            if ($status->syntax_error !== null && $errorMsg === null) {
                $errorMsg = $status->syntax_error;
            }
        }

        if ($failed) {
            throw new RuntimeException($errorMsg ?? 'Unknown PHQL parsing error');
        }

        if (!is_array($status->ret)) {
            throw new RuntimeException('PHQL parsing produced no result');
        }

        return $status->ret;
    }

    /**
     * Wrap a scanner Token into the lightweight token object the parser expects.
     * Mirrors phql_parse_with_token() in base.c.
     */
    private function makeParserToken(Token $token): stdClass
    {
        $pt            = new stdClass();
        $pt->opcode    = $token->opcode;
        $pt->token     = $token->value;
        $pt->token_len = $token->len;
        $pt->free_flag = 1;

        return $pt;
    }

    /**
     * Mirrors phql_scanner_error_msg() in base.c.
     */
    private function buildScannerErrorMsg(stdClass $status, string $phql): string
    {
        $state = $status->scanner_state;

        if ($state->getStart() !== null && $state->startLength > 0) {
            $startStr = substr($phql, $state->getCursor());
            if ($state->startLength > 16) {
                $errorPart = substr($startStr, 0, 16);

                return sprintf(
                    "Scanning error before '%s...' when parsing: %s (%d)",
                    $errorPart,
                    $phql,
                    $status->phql_length
                );
            }

            return sprintf(
                "Scanning error before '%s' when parsing: %s (%d)",
                $startStr,
                $phql,
                $status->phql_length
            );
        }

        return 'Scanning error near to EOF';
    }
}
