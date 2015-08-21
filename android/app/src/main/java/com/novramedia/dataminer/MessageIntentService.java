package com.novramedia.dataminer;

import android.app.IntentService;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.provider.Settings;
import android.util.Log;

import org.apache.http.NameValuePair;
import org.apache.http.client.entity.UrlEncodedFormEntityHC4;
import org.apache.http.client.methods.CloseableHttpResponse;
import org.apache.http.client.methods.HttpPostHC4;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;
import org.apache.http.message.BasicNameValuePair;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.InetAddress;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.ArrayList;
import java.util.List;


/**
 * Created by nathan on 6/24/2015.
 */
public class MessageIntentService extends IntentService {


    private static final String TAG = MessageIntentService.class.getSimpleName();
    private static final String KEY_PREF_SERVER = "server";
    private static final String KEY_PREF_FB_LOGIN_PATH = "fb_login_path";


    public MessageIntentService() {
        super(TAG);
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        Bundle bundle = intent.getExtras();
        if (bundle == null) {
            Log.w(TAG, "Empty intent, doing nothing");
        } else {
            boolean result = post(bundle);
        }
    }


    private boolean post(Bundle bundle) {
        try {
            // Set up connection to server
            Connection c = new Connection();

            // Send POST post
            c.write(bundle);
            String response = c.read();

            Log.w(TAG, response + "    empty??????!!");

//            System.out.println(response);
            // Reuse the write buffer as read buffer
            if (response.contains("success")) {
                Log.w(TAG, "successfully connect to the server/nathan");
                return true;
            } else {
                Log.w(TAG, response);
                return false;
            }
        } catch (IOException e) {
            Log.w(TAG, e);
            return false;
        }
    }


    private class Connection {
        private String domain;
        private int port = 0;
        private String path;

        CloseableHttpResponse response;

        CloseableHttpClient client = HttpClients.createDefault();
        HttpPostHC4 post;


        public Connection() throws IOException, IllegalArgumentException {


            // set up connection to server
            domain = retrieveDomain();
            path = retrievePath();
            port = 80;

            post = new HttpPostHC4(domain + ":" + port + path);

            post.setHeader("Accept-charset", "utf-8");
            post.setHeader("Content-Type", "application/x-www-form-urlencoded");
            post.setHeader("Accept", "application/json");
            post.setHeader("Connection", "keep-alive");
        }

        public boolean write(Bundle bundle) throws IOException {

            // data that's always sent regardless of action
            // uid = Android ID
            String uid = getUid();

            String nodeid = bundle.getString("nodeid");
            String accesstoken = bundle.getString("accesstoken");


            List<NameValuePair> params = new ArrayList<>();


            params.add(new BasicNameValuePair("nodeid", nodeid));
            params.add(new BasicNameValuePair("uid", uid));
            params.add(new BasicNameValuePair("access_token", accesstoken));
            post.setEntity(new UrlEncodedFormEntityHC4(params));


            System.out.println("sending to server " + params);
            System.out.println("post sending to server " + post.toString());

            response = client.execute(post);
            Log.w(TAG, "executed");
            return true;
        }

        public String getDomain() {
            return domain;
        }

        public int getPort() {
            return port;
        }

        public String getPath() {
            return path;
        }


        private String retrieveDomain() {
            SharedPreferences prefs =
                    PreferenceManager.getDefaultSharedPreferences(MessageIntentService.this);
            String domain = prefs.getString(KEY_PREF_SERVER, "http://novramedialabs.com");

            // sanity check
            if (domain == null) {
                domain = "http://novramedialabs.com";
            }


            // Test connection and return the one that works
            try {
                String a = domain.replaceAll("http[s]?://", "");
                InetAddress addr = InetAddress.getByName(a);
                if (addr.isReachable(15000)) {
                    return domain;
                } else {
                    throw new RuntimeException("Cannot connect to " + domain);
                }
            } catch (Exception e) {
                Log.e(TAG, "Error connecting to server", e);
                e.printStackTrace();
                return domain;
            }
        }


        private String retrievePath() {
            SharedPreferences prefs =
                    PreferenceManager.getDefaultSharedPreferences(MessageIntentService.this);
            String path = prefs.getString(KEY_PREF_FB_LOGIN_PATH, "/nathan/nathan.php");

            // sanity check
            if (path == null) {
                path = "/whatever.php";
            }
            return path;
        }

        private String getUid() {
            Context context = MessageIntentService.this;
            MessageDigest digester;
            try {
                digester = MessageDigest.getInstance("MD5");
                digester.update(Settings.Secure.getString(context.getContentResolver(),
                        Settings.Secure.ANDROID_ID).getBytes());
                //digester.update(context.getPackageName().getBytes());
                byte[] digest = digester.digest();
                StringBuilder sb = new StringBuilder();
                for (byte edigest : digest) {
                    sb.append(Integer.toString((edigest & 0xff) + 0x100, 16).substring(1));
                }
                return sb.toString();
            } catch (NoSuchAlgorithmException e) {
                return Settings.Secure.getString(context.getContentResolver(),
                        Settings.Secure.ANDROID_ID);
                //+ context.getPackageName();
            }
        }


        public String read() throws IOException {
            // Read response from server
            BufferedReader reader = new BufferedReader(new InputStreamReader(response.getEntity()
                    .getContent()));
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                sb.append(line);
            }
            return sb.toString();
        }


    }


    private static boolean checkResponse(String response) {
        // Handle response

        if (response.contains("success")) {
            return true;
        } else {
            return false;
        }
    }


}
