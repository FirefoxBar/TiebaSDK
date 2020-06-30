import got from 'got';
import * as md5 from 'js-md5';
import { stringify } from 'qs';
import { v4 as uuid } from 'uuid';

const client: { [x: string]: string } = {
  typeName: 'Android',
  net_type: '3',
  _client_id: `wappc_${Date.now().toString().substr(0, 10)}_${rand(100, 999)}`,
  _client_version: '5.2.2',
  _phone_imei: md5(uuid()),
};

function rand(min: number, max: number) {
  return Math.round(Math.random() * (max - min)) + min;
}

interface FetchUrlData {
  browser?: boolean;
  headers?: { [x: string]: string };
  post?: { [x: string]: string };
}
export async function fetchUrl(url: string, data: FetchUrlData) {
  const clientUA = `BaiduTieba for ${client.typeName} ${client._client_version}`;
  const browserUA = 'Firefox 39.0 Mozilla/5.0 (Windows NT 6.3; rv:39.0) Gecko/20100101 Firefox/39.0'
  const result = await got(url, {
    method: data.post ? 'POST' : 'GET',
    headers: {
      'User-Agent': data.browser ? browserUA : clientUA,
      ...(data.headers || {})
    },
    body: data.post ? stringify(data.post) : undefined
  });
  try {
    return JSON.parse(result.body);
  } catch (e) {
    return result.body;
  }
}

/**
 * 创建贴吧URL
 * @access public
 * @param string $path
 * @param string $prefix 域名前缀
 * @return string
 */
export function createUrl(path: string, prefix: string = 'c') {
  const tburl = 'tieba.baidu.com';
  let r = 'http://';
  if (prefix !== '') {
    r += prefix + '.';
  }
  r += tburl + '/' + path;
  return r;
}

/**
 * 生成客户端签名
 * @access public
 * @param array $data POST数据
 * @return string
 */
export function clientSign(data: { [x: string]: any }) {
  let sign_str = '';
  for (const k in data) {
    sign_str += k;
    sign_str += '=';
    sign_str += data[k];
  }
  return md5(sign_str + 'tiebaclient!!!').toUpperCase();
}

/**
 * 获取tbs
 * @access public
 * @param string $BDUSS BDUSS
 * @param boolean $forceRefresh 是否强制刷新
 * @return string
 */
const tbs: { [x: string]: string } = {};
export async function getTbs(BDUSS: string, forceRefresh: boolean = false) {
  const k = md5(BDUSS);
  if (typeof tbs[k] !== 'undefined' && !forceRefresh) {
    return tbs[k];
  }
  const result = await fetchUrl(createUrl('dc/common/tbs', ''), {
    headers: {
      Cookie: `BDUSS=${BDUSS}`
    }
  });
  tbs[k] = result.tbs;
  return result.tbs;
}

/**
 * 生成客户端信息
 * @access public
 * @param string $k
 * @return string
 */
export function getClient(k: string) {
  if (typeof client[k] !== 'undefined') {
    return client[k];
  }
  return '';
}