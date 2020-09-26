module Auth.Login exposing (..)

import Auth.Layout exposing (authButtonClasses, authContainer, authErrors, authInput, authLink)
import Html exposing (Html, button, div, form, text)
import Html.Attributes exposing (attribute, class, type_)
import Html.Events exposing (onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, Page, page)
import Routes exposing (get, post)


type alias Props =
    { errors : Errors }


type alias State =
    { email : String
    , password : String
    }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetEmail String
    | SetPassword String
    | ForgotPassword
    | Register


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))


stateFromProps : Props -> State
stateFromProps props =
    { email = ""
    , password = ""
    }


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , view = view
        , update = update
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        ForgotPassword ->
            ( { props = props, state = state }, get Routes.passwordUpdate )

        Register ->
            ( { props = props, state = state }, get Routes.register )

        SetEmail newEmail ->
            ( { props = props, state = { state | email = newEmail } }, Cmd.none )

        SetPassword newPassword ->
            ( { props = props, state = { state | password = newPassword } }, Cmd.none )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.login )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "email", Json.Encode.string state.email )
                            , ( "password", Json.Encode.string state.password )
                            ]
                      )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    div []
        [ authContainer "Sign in to your account"
            [ form [ onSubmit Submit ]
                [ div [ class "flex flex-wrap mb-6" ]
                    [ authInput SetEmail state.email props.errors "Email" "email" [ attribute "autofocus" "", attribute "required" "", type_ "email", attribute "autocomplete" "email" ]
                    , authErrors props.errors "email"
                    ]
                , div [ class "flex flex-wrap" ]
                    [ div [ class "flex w-full" ]
                        [ authInput SetPassword state.password props.errors "Password" "password" [ attribute "required" "", type_ "password", attribute "autocomplete" "current-password" ]
                        , authErrors props.errors "password"
                        ]
                    , div [ class "flex items-center mt-8 w-full justify-between" ]
                        [ button [ class authButtonClasses, type_ "submit" ]
                            [ text "Continue" ]
                        , authLink ForgotPassword "text-right" [ text "Forgot Your Password?" ]
                        ]
                    ]
                ]
            ]
        , div [ class "w-full text-center" ]
            [ text "Don't have an account?"
            , authLink Register "ml-6" [ text "Sign up" ]
            ]
        ]
