<?php/** * *    Sappiens Framework *    Copyright (C) 2014, BRA Consultoria * *    Website do autor: www.braconsultoria.com.br/sappiens *    Email do autor: sappiens@braconsultoria.com.br * *    Website do projeto, equipe e documentação: www.sappiens.com.br *    *    Este programa é software livre; você pode redistribuí-lo e/ou *    modificá-lo sob os termos da Licença Pública Geral GNU, conforme *    publicada pela Free Software Foundation, versão 2. * *    Este programa é distribuído na expectativa de ser útil, mas SEM *    QUALQUER GARANTIA; sem mesmo a garantia implícita de *    COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM *    PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais *    detalhes. *  *    Você deve ter recebido uma cópia da Licença Pública Geral GNU *    junto com este programa; se não, escreva para a Free Software *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA *    02111-1307, USA. * *    Cópias da licença disponíveis em /Sappiens/_doc/licenca * *//** * @author Pablo Vanni - pablovanni@gmail.com * @since 28/02/2005 * Última Atualização: 14/10/2014 * Atualizada Por: Pablo Vanni - pablovanni@gmail.com */namespace Zion\Acesso;class AcessoSql{    protected $con;    public function __construct()    {        $this->con = \Zion\Banco\Conexao::conectar();    }    /**     * Retorna o objeto QueryBuilder montando o sql para retornar a descrição      * do módulo indicado pelo parametro     * @param string $moduloNome     * @return \Doctrine\DBAL\Query\QueryBuilder     */    public function dadosModulo($moduloNome)    {        $qb = $this->con->qb();        $qb->select(['moduloCod',                    'grupoCod',                    'moduloCodReferente',                    'moduloNome',                    'moduloNomeMenu',                    'moduloDesc',                    'moduloVisivelMenu',                    'moduloPosicao',                    'moduloBase',                    'moduloClass'])                ->from('_modulo', '')                ->where($qb->expr()->eq('moduloCod', ':moduloNome'))                ->setParameter('moduloNome', $moduloNome, \PDO::PARAM_STR);        return $qb;    }    /**     * Retorna o objeto QueryBuilder montando o sql para verificar      * se o usuário tem permissão para acessar o módulo     * @param string $moduloNome     * @param string $acaoModuloIdPermissao     * @param int $usuarioCod     * @return \Doctrine\DBAL\Query\QueryBuilder     */    public function permissaoAcao($moduloNome, $acaoModuloIdPermissao, $usuarioCod)    {        $qb = $this->con->qb();        $qb->select('a.usuarioCod')                ->from('_usuario', 'a')                ->innerJoin('a', '_perfil', 'b', 'a.perfilCod = b.PerfilCod')                ->innerJoin('b', '_permissao', 'c', 'b.perfilCod = c.PerfilCod')                ->innerJoin('c', '_acao_modulo', 'd', 'c.acaoModuloCod = d.acaoModuloCod')                ->innerJoin('d', '_modulo', 'e', 'd.moduloCod = e.moduloCod')                ->where($qb->expr()->eq('a.usuarioCod', ':usuarioCod'))                ->andWhere($qb->expr()->eq('d.acaoModuloIdPermissao', ':acaoModuloIdPermissao'))                ->andWhere($qb->expr()->eq('e.moduloNome', ':moduloNome'))                ->setParameter('usuarioCod', $usuarioCod, \PDO::PARAM_INT)                ->setParameter('acaoModuloIdPermissao', $acaoModuloIdPermissao, \PDO::PARAM_STR)                ->setParameter('moduloNome', $moduloNome, \PDO::PARAM_STR);//        $qb->select('a.usuarioCod')//                ->from('_permissao', 'a')//                ->innerJoin('a', '_acao_modulo', 'b', 'a.acaoModuloCod = b.acaoModuloCod')//                ->innerJoin('b', '_modulo', 'c', 'c.moduloCod = b.moduloCod')//                ->where($qb->expr()->eq('a.usuarioCod', ':usuarioCod'))//                ->andWhere($qb->expr()->eq('b.acaoModuloIdPermissao', ':acaoModuloIdPermissao'))//                ->andWhere($qb->expr()->eq('c.moduloNome', ':moduloNome'))//                ->setParameter('usuarioCod', $usuarioCod, \PDO::PARAM_INT)//                ->setParameter('acaoModuloIdPermissao', $acaoModuloIdPermissao, \PDO::PARAM_STR)//                ->setParameter('moduloNome', $moduloNome, \PDO::PARAM_STR);        return $qb;    }    /**     * Retorna o objeto QueryBuilder montando o sql para retornar      * as opções de módulo que o usuário tem direito     * @param string $moduloNome     * @param int $usuarioCod     * @return \Doctrine\DBAL\Query\QueryBuilder     */    public function permissoesModulo($moduloNome, $usuarioCod)    {        $qb = $this->con->qb();        $qb->select(['d.acaoModuloCod',                    'd.moduloCod',                    'd.acaoModuloPermissao',                    'd.acaoModuloIdPermissao',                    'd.acaoModuloIcon',                    'd.acaoModuloToolTipComPermissao',                    'd.acaoModuloToolTipeSemPermissao',                    'd.acaoModuloFuncaoJS',                    'd.acaoModuloPosicao',                    'd.acaoModuloApresentacao'])                ->from('_usuario', 'a')                ->innerJoin('a', '_perfil', 'b', 'a.perfilCod = b.PerfilCod')                ->innerJoin('b', '_permissao', 'c', 'b.perfilCod = c.PerfilCod')                ->innerJoin('c', '_acao_modulo', 'd', 'c.acaoModuloCod = d.acaoModuloCod')                ->innerJoin('d', '_modulo', 'e', 'd.moduloCod = e.moduloCod')                ->where($qb->expr()->eq('a.usuarioCod', ':usuarioCod'))                ->andWhere($qb->expr()->eq('e.moduloNome', ':moduloNome'))                ->setParameter('usuarioCod', $usuarioCod, \PDO::PARAM_INT)                ->setParameter('moduloNome', $moduloNome, \PDO::PARAM_STR);        return $qb;    }    /**     * Retorna o objeto QueryBuilder montando o sql para retornar      * os dados de uma ação do módulo     * @param string $moduloNome     * @param string $acaoModuloIdPermissao     * @return \Doctrine\DBAL\Query\QueryBuilder     */    public function dadosAcaoModulo($moduloNome, $acaoModuloIdPermissao)    {        $qb = $this->con->qb();        $qb->select(['a.acaoModuloCod',                    'a.moduloCod',                    'a.acaoModuloPermissao',                    'a.acaoModuloIdPermissao',                    'a.acaoModuloIcon',                    'a.acaoModuloToolTipComPermissao',                    'a.acaoModuloToolTipeSemPermissao',                    'a.acaoModuloFuncaoJS',                    'a.acaoModuloPosicao',                    'a.acaoModuloApresentacao'])                ->from('_acao_modulo', 'a')                ->innerJoin('a', '_modulo', 'b', 'a.moduloCod = b.moduloCod')                ->where($qb->expr()->eq('a.acaoModuloIdPermissao', ':acaoModuloIdPermissao'))                ->andWhere($qb->expr()->eq('b.moduloNome', ':moduloNome'))                ->setParameter(':acaoModuloIdPermissao', $acaoModuloIdPermissao, \PDO::PARAM_STR)                ->setParameter(':moduloNome', $moduloNome, \PDO::PARAM_STR);        return $qb;    }}