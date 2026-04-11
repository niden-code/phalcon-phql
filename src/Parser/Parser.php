<?php

declare(strict_types=1);

namespace Phalcon\Phql\Parser;

use Phalcon\Phql\Exception;
use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Phql\Scanner\Scanner;
use Phalcon\Phql\Scanner\ScannerStatus;
use Phalcon\Phql\Scanner\State;
use Phalcon\Phql\Scanner\Token;
use phql_Parser;

class Parser
{
    private ?Token $token = null;

    private string $debugFile = 'phql.log';

    public function __construct(private readonly bool $debug = false)
    {
    }

    /**
     * @return array<mixed>
     */
    public function parse(string $phql): array
    {
        if (strlen($phql) === 0) {
            return [];
        }

        $debug = null;
        if ($this->debug) {
            $debug = fopen($this->debugFile, 'w+');
        }

        $codeLength  = strlen($phql);
        $parserState = new State($phql);
        $parserStatus = new Status($parserState);
        $parserStatus->setEnableLiterals(true);
        $scanner = new Scanner($parserStatus->getState());

        $parser = new phql_Parser($parserStatus);
        $parser->phql_Trace($debug);

        $state = $parserStatus->getState();
        while (ScannerStatus::OK === ($scannerStatus = $scanner->scanForToken())) {
            $this->token = $scanner->getToken();
            $parserStatus->setToken($this->token);
            $state->setStartLength($codeLength - $state->getCursor());

            $opcode = $this->token->opcode;
            $state->setActiveToken($opcode);

            switch ($opcode) {
                case Opcode::IGNORE:
                    break;

                case Opcode::ADD:
                    $parser->phql_(phql_Parser::PHQL_PLUS);
                    break;

                case Opcode::SUB:
                    $parser->phql_(phql_Parser::PHQL_MINUS);
                    break;

                case Opcode::MUL:
                    $parser->phql_(phql_Parser::PHQL_TIMES);
                    break;

                case Opcode::DIV:
                    $parser->phql_(phql_Parser::PHQL_DIVIDE);
                    break;

                case Opcode::MOD:
                    $parser->phql_(phql_Parser::PHQL_MOD);
                    break;

                case Opcode::AND:
                    $parser->phql_(phql_Parser::PHQL_AND);
                    break;

                case Opcode::OR:
                    $parser->phql_(phql_Parser::PHQL_OR);
                    break;
                case Opcode::EQUALS:
                    $parser->phql_(phql_Parser::PHQL_EQUALS);
                    break;
                case Opcode::NOTEQUALS:
                    $parser->phql_(phql_Parser::PHQL_NOTEQUALS);
                    break;
                case Opcode::LESS:
                    $parser->phql_(phql_Parser::PHQL_LESS);
                    break;
                case Opcode::GREATER:
                    $parser->phql_(phql_Parser::PHQL_GREATER);
                    break;
                case Opcode::GREATEREQUAL:
                    $parser->phql_(phql_Parser::PHQL_GREATEREQUAL);
                    break;
                case Opcode::LESSEQUAL:
                    $parser->phql_(phql_Parser::PHQL_LESSEQUAL);
                    break;
                case Opcode::IDENTIFIER:
                    $this->phqlParseWithToken($parser, Opcode::IDENTIFIER, phql_Parser::PHQL_IDENTIFIER);
                    break;

                case Opcode::DOT:
                    $parser->phql_(phql_Parser::PHQL_DOT);
                    break;
                case Opcode::COMMA:
                    $parser->phql_(phql_Parser::PHQL_COMMA);
                    break;

                case Opcode::PARENTHESES_OPEN:
                    $parser->phql_(phql_Parser::PHQL_PARENTHESES_OPEN);
                    break;
                case Opcode::PARENTHESES_CLOSE:
                    $parser->phql_(phql_Parser::PHQL_PARENTHESES_CLOSE);
                    break;

                case Opcode::LIKE:
                    $parser->phql_(phql_Parser::PHQL_LIKE);
                    break;
                case Opcode::ILIKE:
                    $parser->phql_(phql_Parser::PHQL_ILIKE);
                    break;
                case Opcode::NOT:
                    $parser->phql_(phql_Parser::PHQL_NOT);
                    break;
                case Opcode::BITWISE_AND:
                    $parser->phql_(phql_Parser::PHQL_BITWISE_AND);
                    break;
                case Opcode::BITWISE_OR:
                    $parser->phql_(phql_Parser::PHQL_BITWISE_OR);
                    break;
                case Opcode::BITWISE_NOT:
                    $parser->phql_(phql_Parser::PHQL_BITWISE_NOT);
                    break;
                case Opcode::BITWISE_XOR:
                    $parser->phql_(phql_Parser::PHQL_BITWISE_XOR);
                    break;
                case Opcode::AGAINST:
                    $parser->phql_(phql_Parser::PHQL_AGAINST);
                    break;
                case Opcode::CASE:
                    $parser->phql_(phql_Parser::PHQL_CASE);
                    break;
                case Opcode::WHEN:
                    $parser->phql_(phql_Parser::PHQL_WHEN);
                    break;
                case Opcode::THEN:
                    $parser->phql_(phql_Parser::PHQL_THEN);
                    break;
                case Opcode::END:
                    $parser->phql_(phql_Parser::PHQL_END);
                    break;
                case Opcode::ELSE:
                    $parser->phql_(phql_Parser::PHQL_ELSE);
                    break;
                case Opcode::FOR:
                    $parser->phql_(phql_Parser::PHQL_FOR);
                    break;
                case Opcode::WITH:
                    $parser->phql_(phql_Parser::PHQL_WITH);
                    break;

                case Opcode::INTEGER:
                    if ($parserStatus->getEnableLiterals()) {
                        $this->phqlParseWithToken($parser, Opcode::INTEGER, phql_Parser::PHQL_INTEGER);
                    } else {
                        $parserStatus->setSyntaxError("Literals are disabled in PHQL statements");
                        $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    }
                    break;
                case Opcode::DOUBLE:
                    if ($parserStatus->getEnableLiterals()) {
                        $this->phqlParseWithToken($parser, Opcode::DOUBLE, phql_Parser::PHQL_DOUBLE);
                    } else {
                        $parserStatus->setSyntaxError("Literals are disabled in PHQL statements");
                        $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    }
                    break;
                case Opcode::STRING:
                    if ($parserStatus->getEnableLiterals()) {
                        $this->phqlParseWithToken($parser, Opcode::STRING, phql_Parser::PHQL_STRING);
                    } else {
                        $parserStatus->setSyntaxError("Literals are disabled in PHQL statements");
                        $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    }
                    break;
                case Opcode::TRUE:
                    if ($parserStatus->getEnableLiterals()) {
                        $parser->phql_(phql_Parser::PHQL_TRUE);
                    } else {
                        $parserStatus->setSyntaxError("Literals are disabled in PHQL statements");
                        $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    }
                    break;
                case Opcode::FALSE:
                    if ($parserStatus->getEnableLiterals()) {
                        $parser->phql_(phql_Parser::PHQL_FALSE);
                    } else {
                        $parserStatus->setSyntaxError("Literals are disabled in PHQL statements");
                        $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    }
                    break;
                case Opcode::HINTEGER:
                    if ($parserStatus->getEnableLiterals()) {
                        $this->phqlParseWithToken($parser, Opcode::HINTEGER, phql_Parser::PHQL_HINTEGER);
                    } else {
                        $parserStatus->setSyntaxError("Integers are disabled in PHQL statements");
                        $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    }
                    break;

                case Opcode::NPLACEHOLDER:
                    $this->phqlParseWithToken($parser, Opcode::NPLACEHOLDER, phql_Parser::PHQL_NPLACEHOLDER);
                    break;
                case Opcode::SPLACEHOLDER:
                    $this->phqlParseWithToken($parser, Opcode::SPLACEHOLDER, phql_Parser::PHQL_SPLACEHOLDER);
                    break;
                case Opcode::BPLACEHOLDER:
                    $this->phqlParseWithToken($parser, Opcode::BPLACEHOLDER, phql_Parser::PHQL_BPLACEHOLDER);
                    break;

                case Opcode::FROM:
                    $parser->phql_(phql_Parser::PHQL_FROM);
                    break;
                case Opcode::UPDATE:
                    $parser->phql_(phql_Parser::PHQL_UPDATE);
                    break;
                case Opcode::SET:
                    $parser->phql_(phql_Parser::PHQL_SET);
                    break;
                case Opcode::WHERE:
                    $parser->phql_(phql_Parser::PHQL_WHERE);
                    break;
                case Opcode::DELETE:
                    $parser->phql_(phql_Parser::PHQL_DELETE);
                    break;
                case Opcode::INSERT:
                    $parser->phql_(phql_Parser::PHQL_INSERT);
                    break;
                case Opcode::INTO:
                    $parser->phql_(phql_Parser::PHQL_INTO);
                    break;
                case Opcode::VALUES:
                    $parser->phql_(phql_Parser::PHQL_VALUES);
                    break;
                case Opcode::SELECT:
                    $parser->phql_(phql_Parser::PHQL_SELECT);
                    break;
                case Opcode::AS:
                    $parser->phql_(phql_Parser::PHQL_AS);
                    break;
                case Opcode::ORDER:
                    $parser->phql_(phql_Parser::PHQL_ORDER);
                    break;
                case Opcode::BY:
                    $parser->phql_(phql_Parser::PHQL_BY);
                    break;
                case Opcode::LIMIT:
                    $parser->phql_(phql_Parser::PHQL_LIMIT);
                    break;
                case Opcode::OFFSET:
                    $parser->phql_(phql_Parser::PHQL_OFFSET);
                    break;
                case Opcode::GROUP:
                    $parser->phql_(phql_Parser::PHQL_GROUP);
                    break;
                case Opcode::HAVING:
                    $parser->phql_(phql_Parser::PHQL_HAVING);
                    break;
                case Opcode::ASC:
                    $parser->phql_(phql_Parser::PHQL_ASC);
                    break;
                case Opcode::DESC:
                    $parser->phql_(phql_Parser::PHQL_DESC);
                    break;
                case Opcode::IN:
                    $parser->phql_(phql_Parser::PHQL_IN);
                    break;
                case Opcode::ON:
                    $parser->phql_(phql_Parser::PHQL_ON);
                    break;
                case Opcode::INNER:
                    $parser->phql_(phql_Parser::PHQL_INNER);
                    break;
                case Opcode::JOIN:
                    $parser->phql_(phql_Parser::PHQL_JOIN);
                    break;
                case Opcode::LEFT:
                    $parser->phql_(phql_Parser::PHQL_LEFT);
                    break;
                case Opcode::RIGHT:
                    $parser->phql_(phql_Parser::PHQL_RIGHT);
                    break;
                case Opcode::CROSS:
                    $parser->phql_(phql_Parser::PHQL_CROSS);
                    break;
                case Opcode::FULL:
                    $parser->phql_(phql_Parser::PHQL_FULL);
                    break;
                case Opcode::OUTER:
                    $parser->phql_(phql_Parser::PHQL_OUTER);
                    break;
                case Opcode::IS:
                    $parser->phql_(phql_Parser::PHQL_IS);
                    break;
                case Opcode::NULL:
                    $parser->phql_(phql_Parser::PHQL_NULL);
                    break;
                case Opcode::BETWEEN:
                    $parser->phql_(phql_Parser::PHQL_BETWEEN);
                    break;
                case Opcode::BETWEEN_NOT:
                    $parser->phql_(phql_Parser::PHQL_BETWEEN_NOT);
                    break;
                case Opcode::DISTINCT:
                    $parser->phql_(phql_Parser::PHQL_DISTINCT);
                    break;
                case Opcode::ALL:
                    $parser->phql_(phql_Parser::PHQL_ALL);
                    break;
                case Opcode::CAST:
                    $parser->phql_(phql_Parser::PHQL_CAST);
                    break;
                case Opcode::CONVERT:
                    $parser->phql_(phql_Parser::PHQL_CONVERT);
                    break;
                case Opcode::USING:
                    $parser->phql_(phql_Parser::PHQL_USING);
                    break;
                case Opcode::EXISTS:
                    $parser->phql_(phql_Parser::PHQL_EXISTS);
                    break;

                default:
                    $parserStatus->setStatus(Status::PHQL_PARSING_FAILED);
                    $opcodeValue = $opcode !== null ? $opcode->value : '';
                    $parserStatus->setSyntaxError("Scanner: Unknown opcode %d" . $opcodeValue);
                    break;
            }

            if ($parserStatus->getStatus() === Status::PHQL_PARSING_FAILED) {
                break;
            }
        }

        if (
            $scannerStatus === ScannerStatus::ERR
            || $scannerStatus === ScannerStatus::IMPOSSIBLE
        ) {
            throw new Exception($parserStatus->getSyntaxError() ?? '');
        } elseif ($scannerStatus === ScannerStatus::EOF) {
            $parser->phql_(0);
        }

        /**
         *  Set a unique id for the parsed ast
         * /
         * if (phalcon_globals_ptr->orm.cache_level >= 1) {
         * if (Z_TYPE_P(&parser_status->ret) == IS_ARRAY) {
         * add_assoc_long(&parser_status->ret, "id", phalcon_globals_ptr->orm.unique_cache_id++);
         * }
         * }
         *
         * ZVAL_ZVAL(*result, &parser_status->ret, 1, 1);
         *
         * /**
         *  Store the parsed definition in the cache
         * /
         * if (cache_level >= 0) {
         *
         * if (!phalcon_globals_ptr->orm.parser_cache) {
         * ALLOC_HASHTABLE(phalcon_globals_ptr->orm.parser_cache);
         * zend_hash_init(phalcon_globals_ptr->orm.parser_cache, 0, NULL, ZVAL_PTR_DTOR, 0);
         * }
         *
         * Z_TRY_ADDREF_P(*result);
         *
         * zend_hash_index_update(
         * phalcon_globals_ptr->orm.parser_cache,
         * phql_key,
         * result
         * );
         * }
         *
         * }
         * }
         * }
         */

        $state->setStartLength(0);
        $state->setActiveToken(null);

        if ($parserStatus->getStatus() !== Status::PHQL_PARSING_OK) {
            throw new Exception($parserStatus->getSyntaxError() ?? '');
        }

        /** @var array<mixed> */
        return $parser->getOutput();
    }

    private function phqlParseWithToken(
        phql_Parser $parser,
        Opcode $opcode,
        int $parserCode,
    ): void {
        $newToken = new Token($opcode, $this->token?->value);

        $this->token = $newToken;

        $parser->phql_($parserCode, $newToken);
    }
}
